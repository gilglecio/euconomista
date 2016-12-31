<?php

/**
 * HomeController class
 *
 * @package App\Controller
 * @version v1.0
 *
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses App\Auth\AuthSession
 * @uses Release
 * @uses User
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use User;
use Release;
use Category;

/**
 * Controller responsável pela rota home da aplicação.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class HomeController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Home';

    /**
     * Renderiza a página home da aplicação.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        /**
         * @var User
         */
        if (! $user = User::find(AuthSession::getUserId())) {
            return $this->redirectWithError($response, 'Usuário não localizado.', '/logout');
        }

        $start_date = new \Datetime(date('Y-m-15'));
        $start_date->sub(new \Dateinterval('P3M'));
        
        $months = [];

        /**
         * Monta os meses.
         */
        for ($i=0; $i < 7; $i++) {
            $date = clone $start_date;
            $date->add(new \Dateinterval("P{$i}M"));

            $months[$date->format('Y-m')] = [
                'receita' => 0,
                'despesa' => 0,
            ];
        }

        /**
         * Configuração do gráfico
         * @var array
         */
        $data = [
            [
                'label' => 'Receitas',
                'lineTension' => 0,
                'borderColor' => 'blue',
                'fill' => false,
                'data' => [],
            ],
            [
                'label' => 'Despesas',
                'lineTension' => 0,
                'fill' => false,
                'borderColor' => 'red',
                'data' => [],
            ],
        ];

        /**
         * Adiciona a soma mês a mês de receitas e despesas.
         */
        foreach ($months as $month => $fields) {
            $sum = $this->getSumDataForMonth(date($month . '-01'));
            $data[0]['data'][] = $sum->receita;
            $data[1]['data'][] = $sum->despesa;
        }

        $categorias = [
            'labels' => [],
            'datasets' => []
        ];

        $months = [];

        $colors = ['red', 'blue', 'green', 'yellow', 'black', 'purple', '#9c27b0'];
        $k = 0;

        foreach ($this->getSumPorCategoria() as $key => $value) {

            $months[] = $value['date'];

            if (! isset($colors[$k])) {
                $k = 0;
            }

            if (! isset($categorias['datasets'][$value['category_id']])) {
                $categorias['datasets'][$value['category_id']] = [
                    'label' => $value['category'],
                    'lineTension' => 0,
                    'borderColor' => Category::find($value['category_id'])->getColor(),
                    'fill' => false,
                    'data' => [],
                ];
            }

            $k++;

            $categorias['datasets'][$value['category_id']]['data'][$value['date']] = $value['sum'];
        }

        $months = array_flip(array_flip($months));

        foreach ($months as $key => $value) {
            $months[strtotime(date($value) . '-15')] = date($value . '-15');
            unset($months[$key]);
        }

        ksort($months);
        $values = array_values($months);

        $start = new \Datetime($values[0]);
        $last = new \Datetime($values[count($values)-1]);

        while ($start < $last) {
            $time = $start->getTimestamp();

            if (! isset($months[$time])) {
                $months[$time] = $start->format('Y-m-15');
            }

            $start->add(new \Dateinterval('P1M'));
        }

        sort($months);

        $months = array_map(function ($m) {
            return substr($m, 0, 7);
        }, $months);


        foreach ($categorias['datasets'] as $key => $dataset) {
            foreach ($months as $month) {
                if (! in_array($month, array_keys($dataset['data']))) {
                    $categorias['datasets'][$key]['data'][strtotime(date($month . '-15'))] = 0;
                } else {
                    $categorias['datasets'][$key]['data'][strtotime(date($month . '-15'))] = $dataset['data'][$month];
                    unset($categorias['datasets'][$key]['data'][$month]);
                }
            }

            $categorias['datasets'][$key]['data'] = array_values($categorias['datasets'][$key]['data']);
        }

        $categorias['labels'] = array_values($months);

        $user = $user->to_array();
        $user['first_name'] = explode(' ', $user['name'])[0];

        $categorias['datasets'] = array_values($categorias['datasets']);

        foreach ($categorias['labels'] as $key => $value) {
            $categorias['labels'][$key] = (new \Datetime(date($value . '-15')))->format('M/Y');
        }

        // dd($categorias);

        $this->view->render($response, 'app/home.twig', [
            'title' => $this->title,
            'user' => $user,
            'categorias' => json_encode($categorias),
            'receitas_despesas' => json_encode($data)
        ]);
        
        return $response;
    }

    /**
     * Retorna a soma total dos lançamentos de receita e despesa por mês.
     *
     * @author Gilglécio Santos de Oliveira <gilglecio_765@hotmail.com>
     * @author Fernando Dutra Neres <fernando@inova2b.com.br>
     * @param  string $date
     * @return \stdClass
     */
    private function getSumDataForMonth($date)
    {
        $current = new \Datetime($date);

        $conditions = [
            'order' => 'status asc, data_vencimento asc',
            'conditions' => [
                '((status in (1,2) and data_vencimento between ? and ?) or (data_vencimento < ? and status = 1 and \'' . date('Y-m') .'\' = \'' . $current->format('Y-m') . '\'))',
                $current->format('Y-m-01'),
                $current->format('Y-m-t'),
                $current->format('Y-m-01')
            ]
        ];

        /**
         * @var array
         */
        $rows = Release::find('all', $conditions);

        /**
         * Saldo do mês, receitas menos despesas.
         * @var float
         */
        $sum_receita = $sum_despesa = 0.00;

        if ($rows) {

            /**
             * @var array
             */
            $rows = Release::gridFormat($rows);
            
            foreach ($rows as $key => $row) {
                $sum = $row['_valor_aberto'] + $row['_valor_liquidado'];

                if ($row['natureza'] == 'Receita') {
                    $sum_receita += $sum;
                } else {
                    $sum_despesa += $sum;
                }
            }
        }

        return (object) [
            'receita' => $sum_receita,
            'despesa' => $sum_despesa,
        ];
    }

    private function getSumPorCategoria()
    {
        $categories = array_map(function ($c) {
            return $c->id;
        }, Category::find('all', ['conditions' => ['hexcolor is not null']]));

        $conditions = [
            'select' => 'sum(value) as sum, count(*) as rows, category_id, data_vencimento', 
            'order' => 'status asc, data_vencimento asc',
            'group' => "category_id, date_format(data_vencimento, '%Y-%m')",
            'conditions' => [
                'status in (1,2)' . ($categories ? ' and category_id in (' . implode(',', $categories) . ')' : '')
            ]
        ];

        /**
         * @var array
         */
        $rows = Release::find('all', $conditions);

        return array_map(function ($r) {

            $d = $r->to_array();
            $d['category'] = $r->category->name;
            $d['date'] = $r->data_vencimento->format('Y-m');
            return $d;
        }, $rows);
    }
}

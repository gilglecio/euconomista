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

        $this->view->render($response, 'app/home.twig', [
            'title' => $this->title,
            'user' => $user,
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
}

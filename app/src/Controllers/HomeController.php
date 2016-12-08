<?php

/**
 * @package HomeController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
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

final class HomeController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Home';

    /**
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
            throw new \Exception('Usuário não localizado.');
        }

        $start_date = new \Datetime(date('Y-m-15'));
        $start_date->sub(new \Dateinterval('P6M'));
        $end_date = new \Datetime(date('Y-m-15'));
        $end_date->add(new \Dateinterval('P6M'));


        $rows = Release::find_by_sql(
            "select count(*) as rows, sum(value) as value, natureza, date from (
                select value, natureza, date_format(data_vencimento, '%Y-%m') as date from releases
                where data_vencimento >= '" . $start_date->format('Y-m-d') . "'
                and data_vencimento <= '" . $end_date->format('Y-m-d') . "'
            ) as tmp group by date, natureza order by date asc"
        );

        $data = [];

        for ($i=0; $i < 13; $i++) {
            $date = clone $start_date;
            $date->add(new \Dateinterval("P{$i}M"));

            $data[$date->format('Y-m')] = [
                'receita' => ['value' => 0, 'rows' => 0, 'percentual' => 0],
                'despesa' => ['value' => 0, 'rows' => 0, 'percentual' => 0],
            ];
        }

        $max = 0;

        foreach ($rows as $key => $value) {
            if ($value->value > $max) {
                $max = $value->value;
            }

            $n = [1 => 'receita', 2 => 'despesa'][$value->natureza];

            $data[$value->date][$n]['value'] = $value->value;
            $data[$value->date][$n]['rows'] = $value->rows;
        }

        foreach ($data as $key => $value) {
            $data[$key]['receita']['percentual'] = ($data[$key]['receita']['value'] * 100) / $max;
            $data[$key]['despesa']['percentual'] = ($data[$key]['despesa']['value'] * 100) / $max;
        }

        $this->view->render($response, 'app/home.twig', [
            'title' => $this->title,
            'user' => $user,
            'data' => $data
        ]);
        
        return $response;
    }
}

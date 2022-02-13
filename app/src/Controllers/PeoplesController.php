<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use People;

final class PeoplesController extends Controller
{
    protected $title = 'Pessoas';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/peoples/index.twig', [
            'title' => $this->title,
            'messages' => $this->getMessages(),
            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório das pessoas cadastradas',
            'rows' => People::find('all', ['order' => 'name asc'])
        ]);
        
        return $response;
    }

    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];
        $data['title'] = 'Nova Pessoa';

        if (isset($args['people_id'])) {
            if (! $people = People::find($args['people_id'])) {
                return $this->redirectWithError($response, 'Pessoa não localizada.', '/app/peoples');
            }

            $data['data'] = $people->to_array();
            $data['title'] = 'Editando ' . $people->name;
        }


        $this->view->render($response, 'app/peoples/form.twig', $data);
        
        return $response;
    }

    public function save(Request $request, Response $response, array $args)
    {
        try {
            People::generate([
                'id' => $request->getParsedBodyParam('id'),
                'name' => $request->getParsedBodyParam('name'),
            ]);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/peoples/form');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/peoples');
    }

    public function delete(Request $request, Response $response, array $args)
    {
        try {
            People::remove($args['people_id']);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/peoples');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/peoples');
    }
}

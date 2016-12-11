<?php

/**
 * PeoplesController class
 * 
 * @package App\Controller
 * @version v1.0
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses People
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use People;

/**
 * Reponse pelas rotas de exibição e manipulação de pesosas.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class PeoplesController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Pessoas';

	/**
     * Renderiza a pagina com o lista de pessoa cadastradas.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/peoples/index.twig', [
        	'title' => $this->title,
        	'messages' => $this->getMessages(),
        	'rows' => People::find('all', ['order' => 'name asc'])
        ]);
        
        return $response;
    }

    /**
     * Renderiza o formulário para inclusão e dição de pessoas.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function form(Request $request, Response $response, array $args)
    {
    	$data = ['messages' => $this->getMessages()];

        if (isset($args['people_id'])) {

            if (! $people = People::find($args['people_id'])) {
                return $this->redirectWithError($response, 'Pessoa não localizada.', '/app/peoples');
            }

            $data['data'] = $people->to_array();
        }

    	$data['title'] = $this->title;

        $this->view->render($response, 'app/peoples/form.twig', $data);
        
        return $response;
    }

    /**
     * Recebe o post do formulário de pessoas.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
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

    /**
     * Apaga uma pessoa pelo ID.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
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

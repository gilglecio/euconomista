<?php

/**
 * @package PeoplesController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses People
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use People;

final class PeoplesController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Pessoas';

	/**
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
        	'error' => $this->flash->getMessages()['error'],
        	'rows' => People::find('all')
        ]);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function form(Request $request, Response $response, array $args)
    {
    	$data = $this->flash->getMessages();
    	$data['title'] = 'Adicionar ' . $this->title;

        $this->view->render($response, 'app/peoples/form.twig', $data);
        
        return $response;
    }

    /**
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
	        	'name' => $request->getParsedBodyParam('name'),
	        ]);

        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/peoples/form');
        }

        return $response->withRedirect('/app/peoples');
    }

    /**
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

        return $response->withRedirect('/app/peoples');
    }
}

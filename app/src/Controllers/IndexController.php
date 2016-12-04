<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class IndexController extends Controller
{
	/**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Gerenciador Financeiro';

	/**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'index.twig', [
        	'title' => $this->title
        ]);
        
        return $response;
    }
}

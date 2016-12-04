<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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
        $this->view->render($response, 'app/home.twig', [
        	'title' => $this->title
        ]);
        
        return $response;
    }
}

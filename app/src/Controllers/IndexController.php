<?php

/**
 * IndexController class
 * 
 * @package App\Controller
 * @version v1.0
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Controller responsável pela rota index.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class IndexController extends Controller
{
	/**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Gestor Financeiro Pessoal';

	/**
     * Renderiza a página index do sistema.
     * 
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

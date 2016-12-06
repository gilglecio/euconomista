<?php

/**
 * @package ExtractController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses Release
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Release;

final class ExtractController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Extrato';

	/**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/extract/index.twig', [
        	'title' => $this->title,
        	'rows' => Release::extract()
        ]);
        
        return $response;
    }
}

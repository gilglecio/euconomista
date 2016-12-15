<?php

/**
 * ReportsController class
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
 * Relatórios
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class ReportsController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Relatórios';

    /**
     * Renderiza a pagina dos relatórios.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/reports/index.twig', [
            'title' => $this->title
        ]);
        
        return $response;
    }
}

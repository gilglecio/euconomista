<?php

/**
 * ExtractController class
 *
 * @package App\Controller
 * @version v1.0
 *
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses Release
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Release;

/**
 * Responsável pelo gerenciamento das rotas do extrato.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class ExtractController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Extrato';

    /**
     * Renderiza a página com o extrato financeiro.
     *
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
            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório do extrato financeiro',
            'rows' => Release::extract()
        ]);
        
        return $response;
    }
}

<?php

/**
 * FeaturesController class
 *
 * @package App\Controller
 * @version v1.0
 *
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses App\Auth\AuthSession
 * @uses Anonimous
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use Anonimous;

/**
 * Tela de demostra em detalhes as funcionalidades do sistema.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class FeaturesController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Funcionalidades';

    /**
     * Renderiza a tela com as funcionalidades do sistema.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'features.twig', [
            'title' => $this->title
        ]);
    }
}

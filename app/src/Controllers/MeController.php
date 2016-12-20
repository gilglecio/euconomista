<?php

/**
 * MeController class
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
use ReleaseLog;

/**
 * Relatórios
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class MeController extends Controller
{
    /**
     * Título da página
     *
     * @var string
     */
    protected $title = 'Meu Cadastro';

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
        $this->view->render($response, 'app/me/index.twig', [
            'title' => $this->title
        ]);
        
        return $response;
    }

    /**
     * Gerar o backup da conta do usuário.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function backup(Request $request, Response $response, array $args)
    {
        $rows = [];

        foreach (ReleaseLog::find('all') as $log) {

            if (! in_array($log->action, [ReleaseLog::ACTION_EMISSAO, ReleaseLog::ACTION_LIQUIDACAO])) {
                continue;
            }

            $rows[] = [
                'name' => $log->release->people->name,
                'category' => $log->release->category->name,
                'data_emissao' => $log->release->log_emissao->date->format('d/m/Y'),
                'data_vencimento' => $log->release->data_vencimento->format('d/m/Y'),
                'data_pagamento' => $log->date->format('d/m/Y'),
                'description' => $log->release->description,
                'value' => $log->release->value,
                'value_pago' => $log->value,
                'number' => $log->release->number,
                'status' => $log->release->getStatusName()
            ];
        }

        $this->view->render($response, 'app/me/backup.twig', [
            'title' => $this->title,
            'rows' => $rows
        ]);
    }
}

<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ReleaseLog;
use App\Util\Toolkit;

final class AccountController extends Controller
{
    protected $title = 'Minha Conta';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/me/index.twig', [
            'title' => $this->title
        ]);
        
        return $response;
    }

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
                'value' => $log->release->getFormatValue(),
                'value_pago' => $log->action == ReleaseLog::ACTION_LIQUIDACAO ? Toolkit::showMoney($log->value) : null,
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

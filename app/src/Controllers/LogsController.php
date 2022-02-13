<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use UserLog;

final class LogsController extends Controller
{
    protected $title = 'Logs dos usuários';

    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/logs/index.twig', [
            'title' => $this->title,
            'messages' => $this->getMessages(),
            'success' => $this->getSuccessMessages(),
            'report_footer' => $this->getReportFooter(),
            'report_title' => 'Relatório dos logs dos usuários',
            'rows' => array_map(function ($r) {
                return [
                    'id' => $r->id,
                    'user' => $r->user->name,
                    'description' => $r->description,
                    'date' => $r->created_at->format('d/m/Y à\s H\hi\m'),
                    'action' => $r->action,
                    'restored_at' => $r->restored_at ? $r->restored_at->format('d/m/Y à\s H\hi\m') : null
                ];
            }, UserLog::find('all', ['order' => 'created_at desc', 'limit' => 100]))
        ]);
        
        return $response;
    }

    public function restore(Request $request, Response $response, array $args)
    {
        try {
            UserLog::restore($args['user_log_id']);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/app/logs');
        }

        $this->success('Sucesso!');

        return $response->withRedirect('/app/logs');
    }
}

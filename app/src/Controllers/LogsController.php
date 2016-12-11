<?php

/**
 * LogsController class
 * 
 * @package App\Controller
 * @version v1.0
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses UserLog
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use UserLog;

/**
 * Responsável pelas rotas de acesso e manipulação dos logs dos usuários.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class LogsController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Logs dos usuários';

	/**
     * Renderiza a página com os logs dos usuários.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/logs/index.twig', [
        	'title' => $this->title,
            'messages' => $this->getMessages(),
            'success' => $this->getSuccessMessages(),
        	'rows' => array_map(function ($r) {
        		return [
        			'id' => $r->id,
        			'user' => $r->user->name,
        			'description' => $r->description,
        			'date' => $r->created_at->format('d/m/Y à\s H\hi\m'),
        			'action' => $r->action,
        			'can_restore' => $r->canRestore(),
                    'restored_at' => $r->restored_at ? $r->restored_at->format('d/m/Y à\s H\hi\m') : null
        		];
        	}, UserLog::find('all', ['order' => 'created_at desc']))
        ]);
        
        return $response;
    }

    /**
     * Recebe a solicitação para restauração de um log.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function restore(Request $request, Response $response, array $args)
    {
        try {
        	UserLog::restore($args['user_log_id']);
        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/logs');
        }

        $this->success('Backup restaurado!');

        return $response->withRedirect('/app/logs');
    }
}

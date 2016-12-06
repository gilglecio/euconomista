<?php

/**
 * @package UsersController
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses User
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use User;

final class UsersController extends Controller
{
	/**
	 * Título da página
	 * 
	 * @var string
	 */
	protected $title = 'Usuários';

	/**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
        $this->view->render($response, 'app/users/index.twig', [
        	'title' => $this->title,
            'error' => $this->getErrorMessages(),
        	'rows' => User::find('all')
        ]);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function form(Request $request, Response $response, array $args)
    {
    	$data = $this->flash->getMessages();
    	$data['title'] = $this->title;

        $this->view->render($response, 'app/users/form.twig', $data);
        
        return $response;
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function save(Request $request, Response $response, array $args)
    {
        try {
        	
        	User::generate([
	        	'name' => $request->getParsedBodyParam('name'),
	        	'email' => $request->getParsedBodyParam('email'),
	        	'password' => $request->getParsedBodyParam('password'),
	        ]);

        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/users/form');
        }

        return $response->withRedirect('/app/users');
    }

    /**
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function delete(Request $request, Response $response, array $args)
    {
        try {
        	User::remove($args['user_id']);
        } catch (\Exception $e) {
        	return $this->redirectWithError($response, $e->getMessage(), '/app/users');
        }

        return $response->withRedirect('/app/users');
    }
}

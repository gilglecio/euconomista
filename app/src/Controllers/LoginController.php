<?php

/**
 * LoginController class
 * 
 * @package App\Controller
 * @version v1.0
 * 
 * @uses Psr\Http\Message\ServerRequestInterface
 * @uses Psr\Http\Message\ResponseInterface
 * @uses App\Auth\AuthSession
 * @uses User
 */
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use User;

/**
 * Controller responsável pelas rotas de login e logout.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class LoginController extends Controller
{
	/**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Login';

	/**
     * Renderiza o formulário de login.
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function index(Request $request, Response $response, array $args)
    {
    	$data = $this->flash->getMessages();
    	$data['title'] = $this->title;

        $this->view->render($response, 'login.twig', $data);


        return $response;
    }

    /**
     * Recebe o post do formulário de login. 
     * 
	 * @param Request  $request
	 * @param Response $response
	 * @param array    $args
	 * 
	 * @return Response
	 */
    public function post(Request $request, Response $response, array $args)
    {
		try {
			AuthSession::attemp(
				new User,
				$request->getParsedBodyParam('email'),
				$request->getParsedBodyParam('password')
			);
		} catch (\Exception $e) {
			return $this->redirectWithError($response, $e->getMessage(), '/login');
		}

		return $response->withRedirect('/app');
    }

    /**
     * Desloga o usuário logado do sistema.
     * 
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     */
    public function logout(Request $request, Response $response, array $args)
    {
    	AuthSession::clear();

    	return $response->withRedirect('/login');
    }
}

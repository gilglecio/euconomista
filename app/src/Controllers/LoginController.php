<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use User;

final class LoginController extends Controller
{
	/**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Login';

	/**
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
			$this->logger->info('User auth error: ' . $e->getMessage());
			$this->flash->addMessage('error', $e->getMessage());
			return $response->withRedirect('/login', 406);
		}

		return $response->withRedirect('/app');
    }

    /**
     * Desloga o usuário.
     * 
     * @return Response
     */
    public function logout(Request $request, Response $response, array $args)
    {
    	AuthSession::clear();

    	return $response->withRedirect('/login');
    }
}

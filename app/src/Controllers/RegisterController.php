<?php

/**
 * RegisterController class
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
 * Responsável pelo cadastro externo de usuários.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class RegisterController extends Controller
{
	/**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Cadastro';

	/**
	 * Renderiza o formulário de cadastro de usuário.
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

        $this->view->render($response, 'register.twig', $data);


        return $response;
    }

    /**
     * Recebe o post do formulário de cadastro.
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
			Anonimous::register([
				'name' => $request->getParsedBodyParam('name'),
				'email' => $request->getParsedBodyParam('email'),
				'password' => $request->getParsedBodyParam('password'),
				'confirm_password' => $request->getParsedBodyParam('confirm_password')
			]);
		} catch (\Exception $e) {
			return $this->redirectWithError($response, $e->getMessage(), '/register');
		}

		$this->logger->info('Register: ' . $request->getParsedBodyParam('email'));
		$this->success('Cadastrado! Acesso liberado.');

		return $response->withRedirect('/login');
    }
}

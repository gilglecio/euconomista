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
        $data = ['messages' => $this->getMessages()];
        $data['title'] = $this->title;

        $this->view->render($response, 'register.twig', $data);


        return $response;
    }

    /**
     * Renderiza a política de privacidade do site
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function policy(Request $request, Response $response, array $args)
    {
        $data['title'] = 'Política de privacidade';

        $this->view->render($response, 'policy.twig', $data);

        return $response;
    }

    /**
     * Renderiza os termos de uso do site
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function terms(Request $request, Response $response, array $args)
    {
        $data['title'] = 'Termos de uso';

        $this->view->render($response, 'terms.twig', $data);

        return $response;
    }

    /**
     * Confirma o email do usuário pelo token.
     * Os tokens de confirmação de email duram 24 horas.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function confirmEmail(Request $request, Response $response, array $args)
    {
        try {
            $user = Anonimous::confirmEmail($args['token']);
            $this->success($user->getFirstName() . ', e-mail confirmado.');
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/login');
        }

        return $response->withRedirect('/login');
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
        $user = null;

        try {
            $user = Anonimous::register([
                'name' => $request->getParsedBodyParam('name'),
                'email' => $request->getParsedBodyParam('email'),
                'password' => $request->getParsedBodyParam('password'),
                'confirm_password' => $request->getParsedBodyParam('confirm_password')
            ]);
        } catch (\Exception $e) {
            return $this->redirectWithError($response, $e->getMessage(), '/register');
        }

        try {
            /**
             * Faz o envio do e-mail de confirmação.
             */
            $this->mailer->send(
                'emails/confirm_email.twig',
                [
                    'confirm_url' => APP_URL . '/register/confirm_email/' . $user->confirm_email_token
                ],
                function ($m) use ($user) {
                    $m->to($user->email, $user->name);
                    $m->subject('EuConomista Confirmação de E-Mail');
                    $m->from('no-replay@euconomista.com');
                    $m->fromName('EuConomista');
                }
            );

            $this->success('Sucesso! Email de confirmação enviado.');
        } catch (\Exception $e) {
            $this->success('Sucesso! Mas o envio do email de confirmação falhou.');
            $this->logger->error('Register: ' . $e->getMessage());
        }

        $this->logger->info('Register: ' . $request->getParsedBodyParam('email'));
        

        return $response->withRedirect('/login');
    }
}

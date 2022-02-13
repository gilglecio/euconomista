<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Auth\AuthSession;
use Anonimous;

final class RegisterController extends Controller
{
    protected $title = 'Cadastre-se';

    public function index(Request $request, Response $response, array $args)
    {
        if (AuthSession::isAuthenticated()) {
            return $response->withRedirect('/app');
        }
        
        $data = ['messages' => $this->getMessages()];
        $data['title'] = $this->title;

        $this->view->render($response, 'register.twig', $data);


        return $response;
    }

    public function policy(Request $request, Response $response, array $args)
    {
        $data['title'] = 'Política de privacidade';

        $this->view->render($response, 'policy.twig', $data);

        return $response;
    }

    public function terms(Request $request, Response $response, array $args)
    {
        $data['title'] = 'Termos de uso';

        $this->view->render($response, 'terms.twig', $data);

        return $response;
    }

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
                    $m->subject('Confirmação de cadastro');
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

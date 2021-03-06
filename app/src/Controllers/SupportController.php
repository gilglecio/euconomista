<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use User;
use App\Auth\AuthSession;

final class SupportController extends Controller
{
    protected $title = 'Suporte';

    public function form(Request $request, Response $response, array $args)
    {
        $data = ['messages' => $this->getMessages()];
        $data['title'] = $this->title;

        $this->view->render($response, 'app/support/form.twig', $data);
        
        return $response;
    }

    public function send(Request $request, Response $response, array $args)
    {
        try {

            if (! $user = User::find(AuthSession::getUserId())) {
                return $response->withRedirect('/logout');
            }

            $subject = $request->getParsedBodyParam('subject');
            $message = $request->getParsedBodyParam('message');

            $vm = [
                'title' => $subject,
                'text' => $message
            ];

            /**
             * Faz o envio do e-mail de confirmação.
             */
            $this->mailer->send(
                'emails/generic.twig', $vm, function ($m) use ($subject, $user) {
                    $m->to($user->email, $user->name);
                    $m->subject($subject);
                    $m->from('euconomista@gmail.com');
                    $m->fromName('EuConomista');
                }
            );

            $this->success('Sucesso! Email de contato enviado.');
        } catch (\Exception $e) {
            $this->error('O envio do e-mail de contato falhou.');
            $this->logger->error('Support: ' . $e->getMessage());
        }

        return $response->withRedirect('/app/support');
    }
}

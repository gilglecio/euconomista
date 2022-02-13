<?php

namespace App\Controller;

use Slim\Container;
use User;
use App\Auth\AuthSession;

abstract class Controller
{
    protected $view;

    protected $logger;

    protected $flash;

    protected $mailer;

    protected $title = 'Page Title';

    public function __construct(Container $app)
    {
        $this->view = $app->get('view');
        $this->logger = $app->get('logger');
        $this->flash = $app->get('flash');
        $this->mailer = $app->get('mailer');

        if (AuthSession::isAuthenticated()) {
            
            /**
             * @var User
             */
            if (! $user = User::find(AuthSession::getUserId())) {
                AuthSession::clear();
                header('Location: /');
            }

            /**
             * Recupera os dados do usuário.
             * @var array
             */
            $user_data = $user->to_array();
            $user_data['first_name'] = $user->getFirstName();

            $this->view->offsetSet('user', $user_data);
            $this->view->offsetSet('release_month', date('Y-m'));
        }
    }

    public function getReportFooter()
    {
        return 'EuConomista - Relatório obtido em ' . date('d \d\e M \d\e Y à\s H\hi\m');
    }

    public function redirectWithError($response, $message, $url, $status = 406)
    {
        $this->logger->info(get_called_class() . ': ' . $message);
        $this->error($message);

        return $response->withRedirect($url, $status);
    }

    public function success($message)
    {
        $this->flash->addMessage('success', $message);
    }

    public function error($message)
    {
        $this->flash->addMessage('danger', $message);
    }

    public function getErrorMessages()
    {
        return $this->getMessages('error');
    }

    public function getSuccessMessages()
    {
        return $this->getMessages('success');
    }

    public function getMessages($key = null)
    {
        $messages = $this->flash->getMessages();

        if ($key) {
            return isset($messages[$key]) ? $messages[$key] : null;
        }

        return $messages;
    }
}

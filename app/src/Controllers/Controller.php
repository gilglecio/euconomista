<?php

/**
 * Controller class
 *
 * @package App\Controller
 * @version v1.0
 *
 * @uses Slim\Container
 */
namespace App\Controller;

use Slim\Container;
use User;
use App\Auth\AuthSession;

/**
 * Classe abstrata para ser extendida pelos contrllers.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
abstract class Controller
{
    /**
     * Recebe a instância do Twig.
     * @var \Slim\Views\Twig
     */
    protected $view;

    /**
     * Recebe a instância de Logger.
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * Recebe a instância de Messages.
     * @var \Slim\Flash\Messages
     */
    protected $flash;

    /**
     * Recebe a instância de Mailer.
     * @var \App\Mail\Mailer
     */
    protected $mailer;

    /**
     * Título da página padrão.
     * @var string
     */
    protected $title = 'Page Title';

    /**
     * Recebe a instancia do container do Slim.
     * @param Container $app
     */
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

    /**
     * Retorna a descrição do rodapé do relatório.
     *
     * @author Gilglécio Santos de Oliveira <gilglecio_765@hotmail.com>
     * @author Fernando Dutra Neres <fernando@inova2b.com.br>
     * @return string
     */
    public function getReportFooter()
    {
        return 'EuConomista - Relatório obtido em ' . date('d \d\e M \d\e Y à\s H\hi\m');
    }

    /**
     * Redirect with flash error.
     *
     * @param Psr\Http\Message\ResponseInterface $response
     * @param array                              $message
     * @param string                             $url
     * @param integer                            $status
     * @return Psr\Http\Message\ResponseInterface
     */
    public function redirectWithError($response, $message, $url, $status = 406)
    {
        $this->logger->info(get_called_class() . ': ' . $message);
        $this->error($message);

        return $response->withRedirect($url, $status);
    }

    /**
     * Adiciona uma mensagem do tipo `success` no flash message.
     *
     * @param string $message Mensagem de sucesso.
     * @return void
     */
    public function success($message)
    {
        $this->flash->addMessage('success', $message);
    }

    /**
     * Adiciona uma mensagem do tipo `error` no flash message.
     *
     * @param string $message Mensagem de error.
     * @return void
     */
    public function error($message)
    {
        $this->flash->addMessage('danger', $message);
    }

    /**
     * Retorna as mensagens de erro armazenadas no flash message.
     *
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->getMessages('error');
    }

    /**
     * Retorna as mensagens de sucesso armazenadas no flash message.
     *
     * @return array
     */
    public function getSuccessMessages()
    {
        return $this->getMessages('success');
    }

    /**
     * Retorna mensagens do flash message.
     *
     * @param string $key Nome da chave que a mensagem foi armazenada.
     * @return array Mensagens armazenadas na chave passada.
     */
    public function getMessages($key = null)
    {
        /**
         * @var array
         */
        $messages = $this->flash->getMessages();

        if ($key) {
            return isset($messages[$key]) ? $messages[$key] : null;
        }

        return $messages;
    }
}

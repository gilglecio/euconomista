<?php

namespace App\Controller;

use Slim\Container;
use Psr\Http\Message\ResponseInterface as Response;

class Controller
{
	/**
	 * @var \Slim\Views\Twig
	 */
    protected $view;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Slim\Flash\Messages
     */
    protected $flash;

    /**
     * Título da página
     * 
     * @var string
     */
    protected $title = 'Page Title';

    /**
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->view = $c->get('view');
        $this->logger = $c->get('logger');
        $this->flash = $c->get('flash');
    }

    public function redirectWithError($response, $message, $url, $status = 406)
    {
        $this->logger->info(get_called_class() . ': ' . $message);
        $this->flash->addMessage('error', $message);
        return $response->withRedirect($url, $status);
    }
}
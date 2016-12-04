<?php

/**
 * @package Controller
 * @subpackage App\Controller
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 * 
 * @uses Slim\Container
 */
namespace App\Controller;

use Slim\Container;

abstract class Controller
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
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->view = $app->get('view');
        $this->logger = $app->get('logger');
        $this->flash = $app->get('flash');
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
        $this->flash->addMessage('error', $message);

        return $response->withRedirect($url, $status);
    }
}
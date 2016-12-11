<?php

/**
 * Mailer class
 * 
 * @package App\Mail
 * @version v1.0
 * 
 * @uses Slim\Views\Twig
 * @uses PHPMailer
 */
namespace App\Mail;

use Slim\Views\Twig;
use PHPMailer;

/**
 * Faz o envio dos e-mails.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class Mailer
{
    /**
     * Instância do Twig
     * @var \Slim\Views\Twig
     */
    protected $view;
    
    /**
     * Instância do PHPMailer
     * @var \PHPMailer
     */
    protected $mailer;
    
    /**
     * Recebe a instância do PHPMailer e Twig.
     * 
     * @param Twig      $view
     * @param PHPMailer $mailer
     */
    public function __construct(Twig $view, PHPMailer $mailer)
    {
        $this->view = $view;
        $this->mailer = $mailer;
    }
    
    /**
     * Faz o envio do e-mail.
     * 
     * @param string   $template Caminho do template ".twig"
     * @param array    $data     Variáveis passada para o template
     * @param \Closure $callback
     * @throws \Exception Erro retornado pelo PHPMailer
     * @return void
     */
    public function send($template, array $data, $callback)
    {
        $message = new Message($this->mailer);
        $message->body($this->view->fetch($template, $data));
        
        call_user_func($callback, $message);
        
        if (! $this->mailer->send()) {
            throw new \Exception($this->mailer->ErrorInfo);
        }
    }
}

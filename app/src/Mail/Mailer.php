<?php

namespace App\Mail;

use Slim\Views\Twig;
use PHPMailer;

class Mailer
{
    protected $view;
    
    protected $mailer;
    
    public function __construct(Twig $view, PHPMailer $mailer)
    {
        $this->view = $view;
        $this->mailer = $mailer;
    }
    
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

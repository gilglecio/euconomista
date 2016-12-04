<?php

/**
 * @package Message
 * @subpackage App\Mail
 * @version v1.0
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
namespace App\Mail;

use PHPMailer;

class Message
{
    /**
     * @var \PHPMailer
     */
    protected $mailer;
    
    /**
     * @param PHPMailer $mailer [description]
     */
    public function __construct(PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }
    
    /**
     * Usado para adicionar destinatários.
     * Para cada destinatário, invocar o método.
     * 
     * @param string $address E-mail do destinatário
     * @param string $name    Nome do destinatário
     * @return void
     */
    public function to($address, $name = null)
    {
        $this->mailer->addAddress($address, $name);
    }

    /**
     * Altera o assunto da mensagem.
     * 
     * @param string $subject Assunto do e-mail
     * @return void
     */
    public function subject($subject)
    {
        $this->mailer->Subject = utf8_decode($subject);
    }

    /**
     * Altera o corpo do e-mail.
     * 
     * @param string $body Mensagem no corpo do e-mail
     * @return void
     */
    public function body($body)
    {
        $this->mailer->Body = utf8_decode($body);
    }

    /**
     * Alterar o email do remetente.
     * 
     * @param string $from E-mail de quem está enviando o e-mail
     * @return void
     */
    public function from($from)
    {
        $this->mailer->From = $from;
    }
    
    /**
     * Alterar o nome do remetente.
     * 
     * @param string $from Nome de quem está enviando o e-mail
     * @return void
     */
    public function fromName($fromName)
    {
        $this->mailer->FromName = $fromName;
    }
}

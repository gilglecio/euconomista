<?php

/**
 * Message class
 *
 * @package App\Mail
 * @version v1.0
 * @uses PHPMailer
 */
namespace App\Mail;

use PHPMailer;

/**
 * Responsaǘel pela confecção da mensagem para ser enviada via PHPMailer.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class Message
{
    /**
     * PHPMailer instance.
     *
     * @var \PHPMailer
     */
    protected $mailer;
    
    /**
     * Construtor da classe, recebe a instancia do PHPMailer.
     *
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
        $this->mailer->addAddress($address, utf8_decode($name));
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
        $this->mailer->Body = str_replace("\n", '<br>', utf8_decode($body));
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
     * @param string $fromName Nome de quem está enviando o e-mail
     * @return void
     */
    public function fromName($fromName)
    {
        $this->mailer->FromName = utf8_decode($fromName);
    }
}

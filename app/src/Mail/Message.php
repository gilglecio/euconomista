<?php

namespace App\Mail;

use PHPMailer;

class Message
{
    protected $mailer;
    
    public function __construct(PHPMailer $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function to($address, $name = null)
    {
        $this->mailer->addAddress($address, utf8_decode($name));
    }

    public function subject($subject)
    {
        $this->mailer->Subject = utf8_decode($subject);
    }

    public function body($body)
    {
        $this->mailer->Body = utf8_decode($body);
    }

    public function from($from)
    {
        $this->mailer->From = $from;
    }
    
    public function fromName($fromName)
    {
        $this->mailer->FromName = utf8_decode($fromName);
    }
}

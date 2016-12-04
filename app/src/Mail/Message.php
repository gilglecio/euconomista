<?php

namespace App\Mail;

class Message
{
    protected $mailer;
    
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function to($address)
    {
        $this->mailer->addAddress($address);
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
        $this->mailer->FromName = $fromName;
    }
}

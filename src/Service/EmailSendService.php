<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSendService extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct
    (
        MailerInterface $mailer
    )
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(Email $email)
    {
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }
}
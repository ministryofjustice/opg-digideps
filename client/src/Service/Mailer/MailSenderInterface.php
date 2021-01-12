<?php

namespace App\Service\Mailer;

use App\Model\Email;

interface MailSenderInterface
{
    public function send(Email $email): bool;
}

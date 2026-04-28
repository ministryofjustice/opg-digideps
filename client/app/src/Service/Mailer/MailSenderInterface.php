<?php

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Frontend\Model\Email;

interface MailSenderInterface
{
    public function send(Email $email): bool;
}

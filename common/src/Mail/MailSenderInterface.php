<?php

namespace OPG\Digideps\Common\Mail;

interface MailSenderInterface
{
    public function send(NotifyMail $email): bool;
}

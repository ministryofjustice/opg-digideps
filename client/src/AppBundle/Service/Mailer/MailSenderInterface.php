<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Model\Email;

interface MailSenderInterface
{
    public function send(Email $email, array $groups, $transport);
}

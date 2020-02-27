<?php

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\NotifyException;

class NotifyClientMock extends Client
{
    public function __construct()
    {
    }

    public function sendEmail($emailAddress, $templateId, array $personalisation = array(), $reference = '', $emailReplyToId = NULL)
    {
        if ($emailAddress === 'break@publicguardian.gov.uk') {
            throw new NotifyException('Intentional mock exception');
        } else {
            return [];
        }
    }
}

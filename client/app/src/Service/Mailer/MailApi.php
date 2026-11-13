<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Frontend\Service\Client\RestClient;

final readonly class MailApi implements MailSenderInterface
{
    public function __construct(private RestClient $restClient)
    {
    }

    public function send(NotifyMail $email): bool
    {
        return $this->restClient->apiCall('post', '/notify/send', json_encode($email), 'raw', authenticated: false) === 'ok';
    }
}

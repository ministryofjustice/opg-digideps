<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Service\Mailer;

use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

final readonly class MailApi implements MailSenderInterface
{
    public function __construct(private RestClient $restClient, private RequestStack $stack)
    {
    }

    public function send(NotifyMail $email): bool
    {
        //TODO FOR TESTING ONLY REMOVE BEFORE RELEASE
        $tokenLink = $email->parameters['tokenLink'] ?? null;
        if ($tokenLink !== null) {
            $session = $this->stack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add('notice', $email->parameters['tokenLink'] ?? '');
            }
        }

        return $this->restClient->apiCall('post', '/notify/send', json_encode($email), 'raw', authenticated: false) === 'ok';
    }
}

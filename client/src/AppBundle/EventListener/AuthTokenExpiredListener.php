<?php

namespace AppBundle\EventListener;

use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class AuthTokenExpiredListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (RestClient::HTTP_CODE_AUTHTOKEN_EXPIRED == (int) $exception->getCode()) {
            $response = new RedirectResponse('/login?from=api');
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}

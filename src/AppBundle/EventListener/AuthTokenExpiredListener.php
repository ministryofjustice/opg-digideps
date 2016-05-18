<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\Client\RestClient;

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

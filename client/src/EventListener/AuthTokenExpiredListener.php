<?php

namespace App\EventListener;

use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AuthTokenExpiredListener
{
    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        $exception = $exceptionEvent->getThrowable();

        if (RestClient::HTTP_CODE_AUTHTOKEN_EXPIRED == (int) $exception->getCode()) {
            $response = new RedirectResponse('/login?from=api');
            $exceptionEvent->setResponse($response);
            $exceptionEvent->stopPropagation();
        }
    }
}

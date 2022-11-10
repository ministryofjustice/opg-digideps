<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedExceptionListener
{
    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        $exception = $exceptionEvent->getThrowable();

        if ($exception instanceof HttpException) {
            if (Response::HTTP_UNAUTHORIZED == $exception->getStatusCode()) {
                $response = new RedirectResponse('/login?from=api');
                $exceptionEvent->setResponse($response);
                $exceptionEvent->stopPropagation();
            } elseif (Response::HTTP_FORBIDDEN == $exception->getStatusCode()) {
                $response = new RedirectResponse('/access-denied');
                $exceptionEvent->setResponse($response);
                $exceptionEvent->stopPropagation();
            }
        }
    }
}

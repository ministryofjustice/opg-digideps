<?php

namespace OPG\Digideps\Frontend\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenExceptionListener
{
    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        $exception = $exceptionEvent->getThrowable();

        if ($exception instanceof HttpException) {
            if ($exception->getStatusCode() == Response::HTTP_FORBIDDEN) {
                $response = new RedirectResponse('/access-denied');
                $exceptionEvent->setResponse($response);
                $exceptionEvent->stopPropagation();
            }
        }
    }
}

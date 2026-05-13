<?php

namespace OPG\Digideps\Frontend\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedExceptionListener
{
    public function onKernelException(ExceptionEvent $exceptionEvent)
    {
        $request = $exceptionEvent->getRequest();
        $exception = $exceptionEvent->getThrowable();

        if ($exception instanceof HttpException) {
            if ($exception->getStatusCode() == Response::HTTP_UNAUTHORIZED) {
                $url = '/login?from=api&lastPage=' . urlencode($request->getRequestUri());

                $response = new RedirectResponse($url);
                $exceptionEvent->setResponse($response);
                $exceptionEvent->stopPropagation();
            }
        }
    }
}

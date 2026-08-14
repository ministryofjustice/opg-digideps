<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ApplicationExceptionListener
{
    public function onKernelException(ExceptionEvent $exceptionEvent): void
    {
        $response = new RedirectResponse('/application-error');
        $exceptionEvent->setResponse($response);
        $exceptionEvent->stopPropagation();
    }
}

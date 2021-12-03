<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseHeadersListener
{
    /**
     * Sets additional headers following PEN test results.
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('X-XSS-Protection', '1; mode=block');
        $responseHeaders->set('X-Content-Type', 'nosniff');
        $responseHeaders->set('X-Frame-Options', 'SAMEORIGIN');
    }
}

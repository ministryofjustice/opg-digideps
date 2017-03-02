<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseHeadersListener
{
    /**
     * Sets additional headers following PEN test results.
     * 
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('X-XSS-Protection', '1; mode=block');
        $responseHeaders->set('X-Content-Type', 'nosniff');
        $responseHeaders->set('X-Frame-Options', 'SAMEORIGIN');
    }
}

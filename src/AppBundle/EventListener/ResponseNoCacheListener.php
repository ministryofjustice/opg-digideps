<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ResponseNoCacheListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $responseHeaders->set('Pragma', 'no-cache');
        $responseHeaders->set('Expires', '0');
    }
}

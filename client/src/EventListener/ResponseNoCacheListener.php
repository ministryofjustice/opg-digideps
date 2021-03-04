<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseNoCacheListener
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $responseHeaders->set('Pragma', 'no-cache');
        $responseHeaders->set('Expires', '0');
    }
}

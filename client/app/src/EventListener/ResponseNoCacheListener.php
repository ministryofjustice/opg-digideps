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

        $session = $event->getRequest()->getSession();
        if ($session && $session->has('session_safe_id')) {
            $responseHeaders->set('X-Session-Safe-Id', $session->get('session_safe_id'));
        }
    }
}

<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseNoCacheListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $responseHeaders->set('Pragma', 'no-cache');
        $responseHeaders->set('Expires', '0');

        try {
            $session = $event->getRequest()->getSession();
            if ($session->has('session_safe_id')) {
                $value = $session->get('session_safe_id') ?? 'UnknownSession';
                $responseHeaders->set('X-Session-Safe-Id', $value);
            }
        } catch (SessionNotFoundException) {
            // No session available, skip
        }
    }
}

<?php

namespace OPG\Digideps\Frontend\EventListener;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Bundle\SecurityBundle\Security;

class ResponseNoCacheListener
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $responseHeaders = $event->getResponse()->headers;

        $responseHeaders->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $responseHeaders->set('Pragma', 'no-cache');
        $responseHeaders->set('Expires', '0');

        $responseHeaders->set('X-Authenticated', $this->security->getUser() !== null ? '1' : '0');

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

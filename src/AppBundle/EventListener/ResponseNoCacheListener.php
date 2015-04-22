<?php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Kernel response listener to disable page caching.
 * Disables the browser history navigation after the user logs out
 */
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
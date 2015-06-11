<?php
namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;

class MemcachedSessionHandler extends NativeSessionHandler
{
    public function __construct($host,$port)
    {
        ini_set('session.save_handler', 'memcached');
         
        if (null === $host) {
            ini_set('session.save_path', 'localhost:11211');
        }
        ini_set('session.save_path', $host.':'.$port);
    }
}
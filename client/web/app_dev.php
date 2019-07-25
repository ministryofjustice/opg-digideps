<?php

ini_set('display_errors', 'on');
ini_set('date.timezone', 'Europe/London');

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.

$loader = $loader = require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/AppKernel.php';

Debug::enable();

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = file_exists(__DIR__ . '/../.enableProdMode')
    ? new AppKernel('test', false)
    : new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

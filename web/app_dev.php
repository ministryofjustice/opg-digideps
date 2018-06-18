<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', 'on');

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

//$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
$loader = $loader = require __DIR__.'/../vendor/autoload.php';
// debug not enabled, otherwise conflicting for REST error handler
//Debug::enable();

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = file_exists(__DIR__ . '/../.enableProdMode')
    ? new AppKernel('prod', false)
    : new AppKernel('dev', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

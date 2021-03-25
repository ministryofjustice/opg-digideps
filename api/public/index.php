<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/Kernel.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    /**
     * Excluding notices as they include array undefined index errors which we rely on, e.g. :
     *
     * $ary['test'] = null;
     *
     * Trying to access $ary['test'] would throw a notice error and app error
     */
    Debug::enable(E_ALL & ~E_NOTICE);
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$_SERVER += $_ENV;

if (!empty($_ENV['APP_ENV'])) {
    $_SERVER['APP_ENV'] = $_ENV['APP_ENV'];
} else {
    $_SERVER['APP_ENV'] = $_SERVER['APP_ENV'] ?? 'dev';
}

$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false;

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

<?php

ob_start();
use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/src/Kernel.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    /*
     * Debug mode will throw an error anytime a notice is triggered in the app e.g. undefined index errors
     * which we rely on throughout the app:
     *
     * $ary['test'] = null;
     *
     * Trying to access $ary['test'] throws a notice error and app error (but in prod mode this is ignored).
     *
     * @TODO fix any instance in app we attempt to access an undefined index of an array
     */
    Debug::enable();
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

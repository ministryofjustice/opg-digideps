<?php declare(strict_types=1);

use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__) . '/app/AppKernel.php';

// We don't want to load the .env files if we are running on a server - ENVIRONMENT is only injected via terraform
if (empty($_SERVER['ENVIRONMENT'])) {
    (new Dotenv())->load(dirname(__DIR__).'/frontend.env', dirname(__DIR__).'/admin.env');
}

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
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? false;

$kernel = new AppKernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

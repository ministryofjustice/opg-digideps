<?php
// default params needed for composer post-install scripts
$container->setParameter('env', getenv('FRONTEND_ROLE') ?: 'FRONTEND_ROLE var missing');

if (empty(getenv('FRONTEND_API_CLIENT_SECRET'))) {
    throw new RuntimeException('missing FRONTEND_API_CLIENT_SECRET variable');
}
$container->setParameter('api_client_secret', getenv('FRONTEND_API_CLIENT_SECRET'));

if (empty(getenv('FRONTEND_SESSION_REDIS_DSN'))) {
    throw new RuntimeException('missing FRONTEND_SESSION_REDIS_DSN variable');
}
$container->setParameter('redis_dsn', getenv('FRONTEND_SESSION_REDIS_DSN'));
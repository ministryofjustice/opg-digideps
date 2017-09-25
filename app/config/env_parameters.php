<?php
// default params needed for composer post-install scripts
$container->setParameter('env', getenv('FRONTEND_ROLE') ?: 'FRONTEND_ROLE.default');
$container->setParameter('api_client_secret', getenv('FRONTEND_API_CLIENT_SECRET') ?: 'FRONTEND_API_CLIENT_SECRET.default');
$container->setParameter('redis_dsn', getenv('FRONTEND_SESSION_REDIS_DSN') ?: 'redis://FRONTEND_SESSION_REDIS_DSN.default');

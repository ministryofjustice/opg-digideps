<?php
// default params needed for composer post-install scripts
$container->setParameter('env', getenv('FRONTEND_ROLE') ?: null);
$container->setParameter('api_client_secret', getenv('FRONTEND_API_CLIENT_SECRET') ?: null);
$container->setParameter('redis_dsn', getenv('FRONTEND_SESSION_REDIS_DSN') ?: 'redis://localhost');
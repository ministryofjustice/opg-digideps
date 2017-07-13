<?php
$container->setParameter('env', getenv('FRONTEND_ROLE'));
$container->setParameter('api_client_secret', getenv('FRONTEND_API_CLIENT_SECRET'));
$container->setParameter('redis_dsn', getenv('FRONTEND_SESSION_REDIS_DSN'));
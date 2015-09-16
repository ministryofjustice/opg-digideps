<?php

chdir(__DIR__ . '/../../');

require 'app/bootstrap.php.cache';

require __DIR__ . "/Fixtures.php";
require __DIR__ . "/AbstractTestController.php";

exec('php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test');
exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');
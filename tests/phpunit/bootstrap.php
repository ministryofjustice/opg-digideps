<?php

chdir(__DIR__ . '/../../');

require 'app/bootstrap.php.cache';

// this drops and recreate the db, good solution so far
passthru('php app/console doctrine:schema:drop --force --env=test');
passthru('php app/console doctrine:schema:create --env=test');
//passthru('php app/console doctrine:migrations:migrate --no-interaction');




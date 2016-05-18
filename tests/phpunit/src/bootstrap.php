<?php

chdir(__DIR__.'/../../../');

require 'app/bootstrap.php.cache';

require __DIR__.'/Fixtures.php';
require __DIR__.'/MockeryStub.php';
require __DIR__.'/Controller/AbstractTestController.php';

exec('php app/console cache:clear --env=test');
exec('php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test');
exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');
exec('php app/console doctrine:schema:validate --env=test');
exec('php app/console digideps:add-user deputy@example.org --firstname=test --lastname=deputy --role=2 --password=Abcd1234 --env=test');
exec('php app/console digideps:add-user admin@example.org --firstname=test --lastname=admin  --role=1 --password=Abcd1234 --env=test');

Fixtures::backupDb();

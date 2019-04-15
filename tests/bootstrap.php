<?php

require 'vendor/autoload.php';

require __DIR__ . '/Fixtures.php';
require __DIR__ . '/MockeryStub.php';
require __DIR__ . '/AppBundle/Controller/AbstractTestController.php';


// keep aligned with API_SECRETS_ONE_KEY env var (digi-deps-local-dev repo)
define('API_TOKEN_DEPUTY', 'api-frontend-key');
define('API_TOKEN_ADMIN', 'api-admin-key');

if (empty(getenv('SKIP_RESET_DB'))) {
    exec('php app/console cache:clear --env=test');
    exec('php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test');
    exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');
    exec('php app/console doctrine:fixtures:load --no-interaction --env=test');
    // additional check to see if doctrine schema is ok
    exec('php app/console doctrine:schema:validate --env=test');
} else {
    echo "Db reset skipped. Set SKIP_RESET_DB=0 to undo\n";
}

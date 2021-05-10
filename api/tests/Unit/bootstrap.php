<?php

require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/Fixtures.php';
require __DIR__.'/MockeryStub.php';
require __DIR__.'/Controller/AbstractTestController.php';

// keep aligned with API_SECRETS_*_KEY env var (digi-deps-local-dev repo)
define('API_TOKEN_DEPUTY', getenv('SECRETS_FRONT_KEY'));
define('API_TOKEN_ADMIN', getenv('SECRETS_ADMIN_KEY'));

if (empty(getenv('SKIP_RESET_DB'))) {
    exec('php app/console doctrine:fixtures:load --no-interaction --env=test');
} else {
    echo "Db reset skipped. Set SKIP_RESET_DB=0 to undo\n";
}

<?php

require __DIR__.'/../../vendor/autoload.php';

require __DIR__.'/Fixtures.php';
require __DIR__.'/MockeryStub.php';
require __DIR__.'/Controller/AbstractTestController.php';

// keep aligned with API_SECRETS_*_KEY env var (digi-deps-local-dev repo)
define('API_TOKEN_DEPUTY', getenv('SECRETS_FRONT_KEY'));
define('API_TOKEN_ADMIN', getenv('SECRETS_ADMIN_KEY'));

<?php

use DG\BypassFinals;

$_SERVER['KERNEL_DIR'] = __DIR__ . '/../../app';

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/MockeryStub.php';

BypassFinals::enable();

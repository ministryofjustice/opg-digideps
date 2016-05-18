<?php

chdir(__DIR__.'/../../../');

require 'app/bootstrap.php.cache';

exec('php app/console cache:clear --env=test');

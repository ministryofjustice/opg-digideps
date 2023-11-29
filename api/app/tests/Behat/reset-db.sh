#!/bin/sh
set -e
confd -onetime -backend env
php app/console doctrine:fixtures:load --no-interaction

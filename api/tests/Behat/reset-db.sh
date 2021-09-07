#!/bin/sh
set -e
confd -onetime -backend env
su-exec www-data php app/console doctrine:fixtures:load --no-interaction

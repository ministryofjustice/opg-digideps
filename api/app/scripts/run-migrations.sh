#!/usr/bin/env bash
set -e

php app/console doctrine:migrations:migrate-lock --allow-no-migration --no-interaction
php app/console doctrine:migrations:up-to-date

#!/bin/sh
set -e

touch /tmp/migrating

cleanup() {
    rm -f /tmp/migrating
}

trap cleanup EXIT

php app/console doctrine:migrations:migrate-lock
php app/console doctrine:migrations:up-to-date

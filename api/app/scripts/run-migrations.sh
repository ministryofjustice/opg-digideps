#!/usr/bin/env bash
set -euo pipefail

echo "ADDING MIGRATING SCRIPT"
touch /tmp/migrating

cleanup() {
    rm -f /tmp/migrating
}

echo "TRAP"
trap cleanup EXIT

echo "RUNNING MIGRATE"
php app/console doctrine:migrations:migrate-lock \
    --allow-no-migration \
    --no-interaction

echo "CHECKING UP TO DATE"
php app/console doctrine:migrations:up-to-date

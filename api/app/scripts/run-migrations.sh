#!/usr/bin/env bash
set -euo pipefail

touch /tmp/migrating

cleanup() {
    rm -f /tmp/migrating
}

trap cleanup EXIT

php app/console doctrine:migrations:migrate-lock \
    --allow-no-migration \
    --no-interaction

php app/console doctrine:migrations:up-to-date

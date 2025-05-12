#!/usr/bin/env bash
set -e

php -d memory_limit=1G app/console $@

#!/usr/bin/env bash
set -e

php -d memory_limit=2G app/console $@

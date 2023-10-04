#!/usr/bin/env bash
set -e

# We need below to create the params file on container start
confd -onetime -backend env

php app/console digideps:document-sync $@

#!/usr/bin/env bash
set -e

php app/console digideps:check-csv-uploaded

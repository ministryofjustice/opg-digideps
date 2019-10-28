#!/bin/sh
set -e

export BEHAT_PARAMS="{\"extensions\": {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\": {\"base_url\": \"$NONADMIN_HOST\"}}}"

vendor/bin/behat --config=tests/behat.yml  --stop-on-failure $@

#!/bin/bash

# Script to symlink client to admin
#

# paths (keep traling slash)
CLIENT_PATH=~/www/opg-digi-deps-client/
ADMIN_PATH=~/www/opg-digi-deps-admin/

mkdir -p $ADMIN_PATH"app/config"

listOfNames="app/AppCache.php
app/AppKernel.php
app/autoload.php
app/bootstrap.php.cache
app/console
app/Resources
app/config/config.yml
app/config/config_prod.yml
app/config/config_dev.yml
app/config/config_test.yml
app/config/routing.yml
app/config/routing_dev.yml
app/config/security.yml
app/config/services.yml
app/config/twig.yml
bin
docker
misc
scripts
src
tests
vendor
web
"

for i in $listOfNames
do
    #ln cunlink $ADMIN_PATH"$i"
    ln -s  $CLIENT_PATH"$i" $ADMIN_PATH"$i"
done


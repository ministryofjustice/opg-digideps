#!/bin/bash

# Script to sync client to admin, and update the YML file with admin-specific params (env and redis_dsn)
#
# to use with fswatch:
# brew install fswatch
# fswatch -o ~/www/opg-digi-deps-client | (while read; do sh ~/www/opg-digi-deps-client/scripts/rsyncToAdmin.sh; done)
#
echo File changed !
# paths (keep traling slash)
CLIENT_PATH=~/www/opg-digi-deps-client/
ADMIN_PATH=~/www/opg-digi-deps-admin/


# rsync, delete cache, replace YML params
rsync -va --delete --exclude=.git $CLIENT_PATH $ADMIN_PATH
rm -rf $ADMIN_PATH"app/cache/*"
sed -i -e 's/env: prod/env: admin/g' $ADMIN_PATH"app/config/parameters.yml"
sed -i -e 's/api_client_secret: 123abc-deputy/api_client_secret: 123abc-ad-admin/g' $ADMIN_PATH"app/config/parameters.yml"
sed -i -e 's/redis:\/\/redisfront/redis:\/\/redisadmin/g' $ADMIN_PATH"app/config/parameters.yml"
echo "Admin area synced"

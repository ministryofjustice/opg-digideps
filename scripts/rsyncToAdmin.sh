#!/bin/bash

# Script to sync client to admin, and update the YML file with admin-specific params (env and redis_dsn)
#

# paths (keep traling slash)
CLIENT_PATH=~/www/opg-digi-deps-client/
ADMIN_PATH=~/www/opg-digi-deps-admin/

rm -rf $ADMIN_PATH"app/cache/*"

# copy config from frontend
cp $CLIENT_PATH"app/config/parameters.yml" $ADMIN_PATH"app/config/parameters.yml"

#replace redis and env param
sed -i -e 's/env: prod/env: admin/g' $ADMIN_PATH"app/config/parameters.yml"
sed -i -e 's/redis:\/\/redisfront/redis:\/\/redisadmin/g' $ADMIN_PATH"app/config/parameters.yml"

echo "Done: parameters.yml for admin contains:"
cat $ADMIN_PATH"app/config/parameters.yml" | grep env:
cat $ADMIN_PATH"app/config/parameters.yml" | grep redis_dsn
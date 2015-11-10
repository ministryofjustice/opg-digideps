#!/bin/bash

if [ -z "$1" ]; then 
    echo "You must pass the branch name as argument"
    exit 1
fi

sed -i -e "s/digideps-client.local/www-$1-feature.dd.opg.digital/g" "app/config/parameters.yml"
sed -i -e "s/digideps-admin.local/admin-$1-feature.dd.opg.digital/g" "app/config/parameters.yml"


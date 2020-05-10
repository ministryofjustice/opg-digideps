#!/usr/bin/env bash
set -e

# We need below to create the params file on container start
confd -onetime -backend env

EXITSIGNAL=1

while [ ${EXITSIGNAL} -eq 1 ]; do
  php app/console digideps:document-sync $@
  printf 'Return value is %s' $?
  EXITSIGNAL=$?
done



echo 'hi'
echo "$0"
echo 'hi'
echo "$@"
#su-exec www-data php app/console digideps:document-sync $0 $@

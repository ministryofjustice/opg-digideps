#!/usr/bin/env bash
set -e

# We need below to create the params file on container start
confd -onetime -backend env

while true
do
    echo "performing document sync at: $(date)"
	php app/console digideps:document-sync
	echo "performing checklist sync at: $(date)"
	php app/console digideps:checklist-sync
	sleep 60
done

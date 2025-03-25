#!/usr/bin/env bash
set -e

while true
do
    echo "performing document sync at: $(date)"
	php app/console digideps:document-sync
	echo "performing checklist sync at: $(date)"
	php app/console digideps:checklist-sync
	sleep 60
done

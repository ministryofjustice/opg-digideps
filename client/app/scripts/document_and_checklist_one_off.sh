#!/usr/bin/env bash
set -e

echo "performing document sync at: $(date)"
php app/console digideps:document-sync
echo "performing checklist sync at: $(date)"
php app/console digideps:checklist-sync

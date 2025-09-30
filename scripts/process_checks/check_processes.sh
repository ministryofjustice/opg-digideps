#!/bin/sh

# Space-separated allow list
allow_list="php-fpm ps sh check_processes docker-entrypoi \
gunicorn nginx freshclam clamd file-scanner health-check.sh \
curl runc:[2:INIT] wait-for-db.sh nc php cat \
entrypoint.sh date"

while true; do
    # Get list of running processes (just the command names)
    running_procs=$(ps -eo comm=)

    for proc in $running_procs; do
        found=0
        for allowed in $allow_list; do
            if [ "$proc" = "$allowed" ]; then
                found=1
                break
            fi
        done

        if [ $found -eq 0 ]; then
            echo "[WARNING] Unexpected_Process_Detected -> $proc"
        fi
    done

    sleep 1
done

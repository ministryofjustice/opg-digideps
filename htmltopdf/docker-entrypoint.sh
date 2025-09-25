#!/bin/sh
crond -l 2

# Start process checker in the background
/check_processes.sh &
/usr/local/bin/gunicorn $@

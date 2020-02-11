#!/bin/sh
service cron restart

usr/local/bin/gunicorn --max-requests 1000 $@

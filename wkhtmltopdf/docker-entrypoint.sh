#!/bin/sh
service cron restart

usr/local/bin/gunicorn --max-requests 10000 $@

#!/bin/sh
service cron restart

usr/local/bin/gunicorn $@

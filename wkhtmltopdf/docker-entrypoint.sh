#!/bin/sh
crond -l 2

usr/local/bin/gunicorn $@

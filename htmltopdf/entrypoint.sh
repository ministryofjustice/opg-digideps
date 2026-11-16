#!/bin/sh
set -a
. /etc/gotenberg.env
set +a

exec /usr/bin/gotenberg "$@"

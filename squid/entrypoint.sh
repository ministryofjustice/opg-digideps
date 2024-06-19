#!/bin/sh

set -e

echo "Initializing cache..."
$(which squid) -N -f /etc/squid/squid.conf -z

echo "Starting squid..."
exec $(which squid) -f /etc/squid/squid.conf -NYCd 1 ${EXTRA_ARGS}

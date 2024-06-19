#!/bin/sh


echo "Curling api..."
curl -I http://api/health-check/service
echo "Finished curling"

set -e

echo "Initializing cache..."
$(which squid) -N -f /etc/squid/squid.conf -z

echo "Starting squid..."
exec $(which squid) -f /etc/squid/squid.conf -NYCd 1 ${EXTRA_ARGS}

#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d

cd /app
/sbin/setuser app mkdir -p misc/tmp
/sbin/setuser app bin/behat --suite=admin

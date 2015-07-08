#!/bin/bash
#let's configure environment
/etc/my_init.d/*

cd /app
/sbin/setuser app mkdir -p /app/misc/tmp
/sbin/setuser app bin/behat --suite=deputy


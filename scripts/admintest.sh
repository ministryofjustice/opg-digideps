#!/bin/bash
#let's configure environment
/etc/my_init.d/*

cd /app
/sbin/setuser app bin/behat --suite=admin

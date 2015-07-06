#!/bin/bash
#let's configure environment
/etc/my_init.d/*

cd /app
bin/behat --suite=admin


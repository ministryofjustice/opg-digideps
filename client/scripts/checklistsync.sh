#!/usr/bin/env bash
set -e

# We need below to create the params file on container start
confd -onetime -backend env

# todo call console command here
echo Hello World

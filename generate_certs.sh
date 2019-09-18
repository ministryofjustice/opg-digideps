#!/usr/bin/env bash
openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout .certs/admin.digideps.local.key \
    -new \
    -out .certs/admin.digideps.local.crt \
    -subj /CN=\admin.digideps.local \
    -sha256 \
    -days 3650

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout .certs/www.digideps.local.key \
    -new \
    -out .certs/www.digideps.local.crt \
    -subj /CN=\www.digideps.local \
    -sha256 \
    -days 3650

openssl req \
    -newkey rsa:4096 \
    -x509 \
    -nodes \
    -keyout .certs/digideps.local.key \
    -new \
    -out .certs/digideps.local.crt \
    -subj /CN=\digideps.local \
    -sha256 \
    -days 3650

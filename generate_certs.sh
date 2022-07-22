#!/usr/bin/env bash
if [ ! -d .certs ]; then
  mkdir -p .certs;
fi

openssl req -x509 -keyout .certs/admin.digideps.local.key -new -out .certs/admin.digideps.local.crt \
    -newkey rsa:4096 -nodes -sha256 -days 3650 \
    -subj '/CN=admin.digideps.local' -extensions EXT -config <( \
    printf "[dn]\nCN=admin.digideps.local\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:admin.digideps.local\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth" \
    )

openssl req -x509 -keyout .certs/digideps.local.key -new -out .certs/digideps.local.crt \
    -newkey rsa:4096 -nodes -sha256 -days 3650 \
    -subj '/CN=digideps.local' -extensions EXT -config <( \
    printf "[dn]\nCN=digideps.local\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:digideps.local\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth" \
    )

openssl req -x509 -keyout .certs/www.digideps.local.key -out .certs/www.digideps.local.crt \
    -newkey rsa:4096 -nodes -sha256 -days 3650 \
    -subj '/CN=www.digideps.local' -extensions EXT -config <( \
    printf "[dn]\nCN=www.digideps.local\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:www.digideps.local\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth" \
    )

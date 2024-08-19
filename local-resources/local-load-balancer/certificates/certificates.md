To create your self signed certificates, make sure you are in the certificates directory
(/docker/local-load-balancer/certificates) and then run the below specified commands :

```
openssl genpkey -algorithm RSA -out nginx.key
```

```
openssl req -new -key nginx.key -out nginx.csr
```

Fill in the following (CN is the only important one but we can add the others too):

- Country Name: UK
- State: (leave blank)
- Locality: (leave blank)
- Organization Name: Ministry of Justice
- Organization Unit Name: Office of the Public Guardian
- Common Name: *.digideps.local
- Email Address: developer@public.guardian.gov.uk
- Password: (leave blank)
- Company name: (leave blank)

Remove passphrase:

```
cp nginx.key nginx.key.org
openssl rsa -in nginx.key.org -out nginx.key
```

Create the certificate with SAN added. Newer versions of chrome need this

```
openssl x509 -req -in nginx.csr -signkey nginx.key -out nginx.crt -days 3650 -sha256 -extfile v3.ext
```

If you are on a Mac, open Keychain access (command+space and search for it).
Drag the nginx.crt from your IDE window into the System folder of Keychain access. It should show an entry of
`*.digideps.local` with a red cross on it. Double click it, go to `trust` and set it to `Always Trust`.

If your load-balancer is already up the make sure you rebuild it and bring it up again so that you get the new certs!

```
docker compose build --no-cache load-balancer
docker compose up -d load-balancer
```

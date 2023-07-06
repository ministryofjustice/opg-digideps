To create your self signed certificates:

```openssl genpkey -algorithm RSA -out nginx.key```

```openssl req -new -key nginx.key -out nginx.csr```

```openssl x509 -req -signkey nginx.key -in nginx.csr -out nginx.crt```

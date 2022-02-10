FROM alpine:3.13 as build-env

RUN apk --update --no-cache add \
    openssl \
    && rm -rf /var/cache/apk/*

# Generate certificate
RUN mkdir -p /etc/nginx/certs
RUN openssl req -newkey rsa:4096 -x509 -nodes -keyout /etc/nginx/certs/app.key -new -out /etc/nginx/certs/app.crt -subj "/C=GB/ST=GB/L=London/O=OPG/OU=Digital/CN=default" -sha256 -days "3650"

FROM nginx:latest

COPY --from=build-env /etc/nginx/certs/app.key /etc/nginx/certs/app.key
COPY --from=build-env /etc/nginx/certs/app.crt /etc/nginx/certs/app.crt

EXPOSE 80
COPY nginx.conf /etc/nginx/nginx.conf

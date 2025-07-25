# Build Stage
FROM golang:1.24.4-alpine3.22 AS build
WORKDIR /go/src/clamav-rest

COPY file-scanner/. .
RUN go mod vendor && go build -v -o file-scanner

# Final Runtime Stage
FROM alpine:3.22

WORKDIR /app

# Environment variables for ClamAV
ENV MAX_SCAN_SIZE=100M
ENV MAX_FILE_SIZE=25M
ENV MAX_RECURSION=16
ENV MAX_FILES=10000
ENV MAX_EMBEDDEDPE=10M
ENV MAX_HTMLNORMALIZE=10M
ENV MAX_HTMLNOTAGS=2M
ENV MAX_SCRIPTNORMALIZE=5M
ENV MAX_ZIPTYPERCG=1M
ENV MAX_PARTITIONS=50
ENV MAX_ICONSPE=100
ENV PCRE_MATCHLIMIT=100000
ENV PCRE_RECMATCHLIMIT=2000
ENV SIGNATURE_CHECKS=2

# Install ClamAV
RUN apk update && apk --no-cache add clamav clamav-libunrar \
    && mkdir /run/clamav \
    && chown clamav:clamav /run/clamav \
    && apk upgrade

# Configure ClamAV to run in foreground with port 3310
RUN sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#TCPSocket .*$/TCPSocket 3310/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/freshclam.conf

# Run initial ClamAV signature update
RUN freshclam --quiet

COPY --from=build /go/src/clamav-rest/file-scanner /usr/bin/

COPY file-scanner/entrypoint.sh /app/entrypoint.sh
RUN chmod +x /app/entrypoint.sh

# Harden security
COPY scripts/hardening/harden.sh /app/harden.sh
RUN /app/harden.sh clamav && rm /app/harden.sh

EXPOSE 8080

USER clamav

ENTRYPOINT [ "/app/entrypoint.sh" ]

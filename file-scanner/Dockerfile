FROM golang:1.22.9-alpine3.20 AS build

# Build go package
ADD file-scanner/. /go/src/clamav-rest/
RUN cd /go/src/clamav-rest && go mod vendor && go build -v

FROM alpine:3.20

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

# Copy compiled clamav-rest binary from build container to production container
COPY --from=build /go/src/clamav-rest/file-scanner /usr/bin/

# Install ClamAV
RUN apk update && apk --no-cache add clamav clamav-libunrar \
    && mkdir /run/clamav \
    && chown clamav:clamav /run/clamav \
    && apk upgrade

# Configure clamAV to run in foreground with port 3310
RUN sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#TCPSocket .*$/TCPSocket 3310/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/freshclam.conf

RUN freshclam --quiet

COPY file-scanner/entrypoint.sh /usr/bin/
COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh clamav && rm /harden.sh

EXPOSE 8080
USER clamav
ENTRYPOINT [ "entrypoint.sh" ]

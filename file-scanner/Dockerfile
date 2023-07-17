FROM golang:1.20.4-alpine3.16 as build

# Build go package
ADD . /go/src/clamav-rest/
RUN cd /go/src/clamav-rest && go mod vendor && go build -v

FROM alpine:3.18

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
    && apk upgrade openssl \
    && apk upgrade libcurl

# Configure clamAV to run in foreground with port 3310
RUN sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#TCPSocket .*$/TCPSocket 3310/g' /etc/clamav/clamd.conf \
    && sed -i 's/^#Foreground .*$/Foreground true/g' /etc/clamav/freshclam.conf

RUN freshclam --quiet --no-dns

COPY entrypoint.sh /usr/bin/

EXPOSE 8080
USER clamav
ENTRYPOINT [ "entrypoint.sh" ]

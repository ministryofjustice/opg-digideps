##############################
# Stage 0.1: Build the base gotenberg
##############################
FROM gotenberg/gotenberg:8.39.0-chromium@sha256:558943677cf9092df83ff654c2645856dcb9ddf777f359243789688e74f3bcdd AS source


##############################
# Stage 0.1: Build the base alpine
##############################
FROM alpine:3.24.4@sha256:f59abe656b692d901e184be3445e084786602108599d2a51d229c0f5a48c71bf as alpine-base
# TODO Remove as temporary workaround until Alpine ships the patched package
# For musl vuln apparently 1.2.6-r3 has the sec patch
RUN apk update && apk add --upgrade \
    "zlib==1.4-r0"


##############################
# Stage X: Upgrade pinned apk packages (used only as target for pinned upgrades)
##############################
FROM alpine-base AS upgrade-pinned-apk-packages
USER root
COPY htmltopdf/Dockerfile .
COPY scripts/upgrade_apk/check-apk-ver.sh .

CMD sh -c "./check-apk-ver.sh"


##############################
# Stage 1.0: Patch vulnerabilities in gotenberg main go app
##############################
FROM golang:1.27.3-trixie@sha256:fbb7ae40b62ff8afe31662df570d39b4f5052ac32b90dae8e60d7657a20a74f4 AS go-patch

ARG BROTOLI_COMMIT=33c2a9884269798192b5ecf171288f98939dbe42
ARG ECHO_COMMIT=a6a9673a54d2c719ceb32ce6a929d35483acfb2b
ARG GO_JOSE_COMMIT=a0111f82742fa92bceec9ede40845e73319e6230
ARG GOTENBERG_VERSION=8.39.0
ARG KLAUSPOST_COMPRESS_COMMIT=7f5d8528720c895a6106f201719f87eb030a565d
ARG PDFCPU_VERSION=v0.15.0
ARG X_NET_COMMIT=3de643db506517b3e652a13cc0b56034cea6b8e6

ENV CGO_ENABLED=0

ADD https://github.com/gotenberg/gotenberg.git#e4ee0f29d648f8d5a0a766dd797d108361905deb /src/gotenberg
ADD https://github.com/pdfcpu/pdfcpu.git#f2686555086a2e76dc19f602ea1897f6e3baae4d /src/pdfcpu

WORKDIR /src/gotenberg

RUN go get \
        github.com/labstack/echo/v5@${ECHO_COMMIT} \
        golang.org/x/net@${X_NET_COMMIT} \
        github.com/klauspost/compress@${KLAUSPOST_COMPRESS_COMMIT} \
        github.com/go-jose/go-jose/v4@${GO_JOSE_COMMIT} \
        github.com/andybalholm/brotli@${BROTOLI_COMMIT} \
    && go mod tidy \
    && go mod download \
    && go mod verify \
    && go build \
        -o /out/gotenberg-chromium \
        -ldflags "-s -w -X 'github.com/gotenberg/gotenberg/v8/cmd.Version=${GOTENBERG_VERSION}'" \
        cmd/gotenberg-chromium/main.go


##############################
# Stage 2.0: Main build of final image
##############################
FROM alpine-base

RUN apk update && apk add --no-cache \
    "ca-certificates==20261020-r0" \
    "chromium==156.0.8078.79-r0"

# Runtime assets from official image
COPY --from=source /opt/gotenberg /opt/gotenberg

# Patched binaries
COPY --from=go-patch /out/gotenberg-chromium /usr/bin/gotenberg

RUN adduser -D -u 1001 gotenberg \
 && chown -R gotenberg:gotenberg \
      /home/gotenberg \
      /opt/gotenberg

# We don't use any of the pdf engines but no way to remove so we fake the executable with fakesh
RUN mkdir /etc/fakebin \
    && touch /etc/fakebin/fakesh \
    && chown gotenberg:gotenberg /etc/fakebin/fakesh \
    && chmod 400 /etc/fakebin/fakesh

# Fake engine executable path variables
ENV PDFCPU_BIN_PATH=/etc/fakebin/fakesh
ENV PDFTK_BIN_PATH=/etc/fakebin/fakesh
ENV QPDF_BIN_PATH=/etc/fakebin/fakesh
ENV EXIFTOOL_BIN_PATH=/etc/fakebin/fakesh

# Gotenberg Settings
ENV CHROMIUM_BIN_PATH=/usr/bin/chromium
ENV CHROMIUM_HYPHEN_DATA_DIR_PATH=/opt/gotenberg/chromium-hyphen-data
ENV GOTENBERG_VERSIONS_DIR_PATH=/opt/gotenberg/versions
ENV HOME=/home/gotenberg
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8
ENV PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
ENV TZ=UTC

WORKDIR /home/gotenberg

COPY --chown=gotenberg:gotenberg htmltopdf/gotenberg.env /etc/gotenberg.env
COPY --chown=gotenberg:gotenberg htmltopdf/entrypoint.sh /entrypoint.sh
RUN chmod 0755 /entrypoint.sh

# Fake file to remove telemetry warnings
RUN mkdir -p /var/lib/dbus \
    && touch /var/lib/dbus/machine-id \
    && chown gotenberg:gotenberg /var/lib/dbus/machine-id \
    && chmod 400 /var/lib/dbus/machine-id

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh gotenberg && rm /harden.sh

USER gotenberg

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh", "--api-port=8080"]

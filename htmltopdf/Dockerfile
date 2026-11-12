# Runtime assets source
FROM gotenberg/gotenberg:8.39.0-chromium@sha256:558943677cf9092df83ff654c2645856dcb9ddf777f359243789688e74f3bcdd AS source

# Patch pdftk-all.jar
FROM alpine:3.24.3 AS jar-patch

ARG COMMONS_LANG3_VERSION=3.18.0
ARG COMMONS_LANG3_SHA1=fb14946f0e39748a6571de0635acbe44e7885491

RUN apk add --no-cache curl zip unzip

COPY --from=source /usr/bin/pdftk-all.jar /work/pdftk-all.jar

WORKDIR /work

RUN set -eux; \
    curl -fsSLo cl3.jar \
        https://repo1.maven.org/maven2/org/apache/commons/commons-lang3/${COMMONS_LANG3_VERSION}/commons-lang3-${COMMONS_LANG3_VERSION}.jar; \
    echo "${COMMONS_LANG3_SHA1}  cl3.jar" | sha1sum -c -; \
    mkdir new; \
    unzip -q cl3.jar -d new 'org/*' 'META-INF/maven/org.apache.commons/*'; \
    zip -q -d pdftk-all.jar \
        'org/apache/commons/lang3/*' \
        'META-INF/maven/org.apache.commons/commons-lang3/*'; \
    (cd new && zip -q -r ../pdftk-all.jar .)

# Build patched gotenberg/pdfcpu
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

WORKDIR /src/pdfcpu

RUN go mod download \
    && go mod verify \
    && go build \
        -o /out/pdfcpu \
        -ldflags "-s -w \
            -X 'main.version=${PDFCPU_VERSION}' \
            -X 'github.com/pdfcpu/pdfcpu/pkg/pdfcpu/model.VersionStr=${PDFCPU_VERSION}' \
            -X main.builtBy=gotenberg" \
        ./cmd/pdfcpu \
    && /out/pdfcpu version

# Alpine runtime
FROM alpine:3.24

RUN apk add --no-cache \
    bash \
    ca-certificates \
    chromium \
    exiftool \
    font-noto \
    font-noto-cjk \
    font-noto-emoji \
    openjdk21-jre \
    qpdf \
    ttf-dejavu \
    tzdata

# Runtime assets from official image
COPY --from=source /opt/gotenberg /opt/gotenberg

# Patched binaries
COPY --from=go-patch /out/gotenberg-chromium /usr/bin/gotenberg
COPY --from=go-patch /out/pdfcpu /usr/bin/pdfcpu
COPY --from=jar-patch /work/pdftk-all.jar /usr/bin/pdftk-all.jar

# pdftk wrapper
RUN printf '#!/bin/bash\nexec java -jar /usr/bin/pdftk-all.jar "$@"\n' \
    > /usr/bin/pdftk \
 && chmod 0755 /usr/bin/pdftk

RUN adduser -D -u 1001 gotenberg \
 && chown -R gotenberg:gotenberg \
      /home/gotenberg \
      /opt/gotenberg

ENV CHROMIUM_BIN_PATH=/usr/bin/chromium
ENV CHROMIUM_HYPHEN_DATA_DIR_PATH=/opt/gotenberg/chromium-hyphen-data
ENV EXIFTOOL_BIN_PATH=/usr/bin/exiftool
ENV GOTENBERG_VERSIONS_DIR_PATH=/opt/gotenberg/versions
ENV HOME=/home/gotenberg
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8
ENV OTEL_LOGS_EXPORTER=none
ENV OTEL_METRICS_EXPORTER=none
ENV OTEL_TRACES_EXPORTER=none
ENV PATH=/opt/java/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
ENV PDFCPU_BIN_PATH=/usr/bin/pdfcpu
ENV PDFENGINES_CONVERT_ENGINES=
ENV PDFTK_BIN_PATH=/usr/bin/pdftk
ENV QPDF_BIN_PATH=/usr/bin/qpdf
ENV TZ=UTC

WORKDIR /home/gotenberg

USER gotenberg

EXPOSE 8080

ENTRYPOINT ["/usr/bin/gotenberg", "--api-port=8080"]

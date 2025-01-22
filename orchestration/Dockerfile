FROM golang:1.23-alpine3.20 AS builder
WORKDIR /app
COPY anonymisation anonymisation
WORKDIR /app/anonymisation
RUN go build -o anonymise main.go
WORKDIR /app
COPY sleep_mode sleep_mode
WORKDIR /app/sleep_mode
RUN go build -o environment_status main.go

FROM alpine:3.20
WORKDIR /
RUN apk update && apk upgrade

ENV POSTGRES_DATABASE **None**
ENV POSTGRES_HOST **None**
ENV POSTGRES_PORT 5432
ENV POSTGRES_USER **None**
ENV POSTGRES_PASSWORD **None**
ENV POSTGRES_EXTRA_OPTS ''
ENV S3_BUCKET **None**
ENV S3_ENDPOINT **None**
ENV S3_PREFIX **None**
ENV S3_S3V4 yes

# install DB and JS tooling
RUN apk add --no-cache --update postgresql python3 py3-pip
RUN apk add --no-cache --update nodejs npm
RUN apk add --no-cache --update aws-cli
RUN apk add --no-cache \
    udev \
    ttf-freefont \
    chromium
RUN apk update && apk upgrade

RUN mkdir certs && chmod 755 certs && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

COPY backup.sh backup.sh
COPY restore.sh restore.sh
COPY common.sh common.sh
COPY analyse-database.sh analyse-database.sh

COPY --from=builder /app/sleep_mode/environment_status .
RUN chmod +x environment_status

WORKDIR /anonymisation
COPY --from=builder /app/anonymisation .
RUN chmod +x anonymise

WORKDIR /tests
COPY tests/package.json package.json
COPY tests/package-lock.json package-lock.json
COPY tests/run-smoke-tests.sh run-smoke-tests.sh
COPY tests/run-resilience-tests.sh run-resilience-tests.sh
RUN npm ci

WORKDIR /tests/smoke-tests
COPY tests/smoke-tests/AdminSmokeTest.js AdminSmokeTest.js
COPY tests/smoke-tests/FrontSmokeTest.js FrontSmokeTest.js

WORKDIR /tests/resilience-tests
COPY tests/resilience-tests/FrontLoadTest.js FrontLoadTest.js
COPY tests/resilience-tests/Analyse.js Analyse.js
COPY tests/resilience-tests/RunExperiments.js RunExperiments.js

WORKDIR /tests/utility
COPY tests/utility/Utility.js Utility.js
COPY tests/utility/DataAnalysis.js DataAnalysis.js

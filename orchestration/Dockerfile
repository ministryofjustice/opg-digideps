FROM golang:1.23-alpine3.20 AS builder

COPY sleep_mode /app/sleep_mode
WORKDIR /app/sleep_mode
RUN go build -o environment_status main.go

COPY anonymisation /app/anonymisation
WORKDIR /app/anonymisation
RUN go build -o anonymise main.go

FROM alpine:3.20
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

COPY backup.sh /backup.sh
COPY restore.sh /restore.sh
COPY common.sh /common.sh
COPY analyse-database.sh /analyse-database.sh

COPY --from=builder /app/sleep_mode/environment_status /
RUN chmod +x environment_status

COPY --from=builder /app/anonymisation /anonymisation/.
RUN chmod +x /anonymisation/anonymise

COPY tests/package.json /tests/package.json
COPY tests/package-lock.json /tests/package-lock.json
COPY tests/run-smoke-tests.sh /tests/run-smoke-tests.sh
COPY tests/run-resilience-tests.sh /tests/run-resilience-tests.sh

COPY tests/smoke-tests/AdminSmokeTest.js /tests/smoke-tests/AdminSmokeTest.js
COPY tests/smoke-tests/FrontSmokeTest.js /tests/smoke-tests/FrontSmokeTest.js

COPY tests/resilience-tests/FrontLoadTest.js /tests/resilience-tests/FrontLoadTest.js
COPY tests/resilience-tests/Analyse.js /tests/resilience-tests/Analyse.js
COPY tests/resilience-tests/RunExperiments.js /tests/resilience-tests/RunExperiments.js

COPY tests/utility/Utility.js /tests/utility/Utility.js
COPY tests/utility/DataAnalysis.js /tests/utility/DataAnalysis.js

WORKDIR /tests
RUN npm ci

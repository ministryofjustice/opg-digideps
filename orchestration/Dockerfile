FROM golang:1.23-alpine3.20 AS builder
WORKDIR /app
COPY anonymisation anonymisation
RUN cd anonymisation && go build -o anonymise main.go

COPY sleep_mode sleep_mode
RUN cd sleep_mode && go build -o environment_status main.go

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

ADD tests/package.json tests/package.json
ADD tests/package-lock.json tests/package-lock.json
RUN cd tests && npm ci
RUN mkdir certs && chmod 755 certs && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

COPY --from=builder /app/sleep_mode/environment_status .
RUN chmod +x environment_status
COPY --from=builder /app/anonymisation /anonymisation
RUN chmod +x /anonymisation/anonymise

ADD tests/smoke-tests/AdminSmokeTest.js tests/smoke-tests/AdminSmokeTest.js
ADD tests/smoke-tests/FrontSmokeTest.js tests/smoke-tests/FrontSmokeTest.js
ADD tests/resilience-tests/FrontLoadTest.js tests/resilience-tests/FrontLoadTest.js
ADD tests/resilience-tests/Analyse.js tests/resilience-tests/Analyse.js
ADD tests/resilience-tests/RunExperiments.js tests/resilience-tests/RunExperiments.js
ADD tests/utility/Utility.js tests/utility/Utility.js
ADD tests/utility/DataAnalysis.js tests/utility/DataAnalysis.js
ADD tests/run-smoke-tests.sh tests/run-smoke-tests.sh
ADD tests/run-resilience-tests.sh tests/run-resilience-tests.sh
ADD backup.sh backup.sh
ADD restore.sh restore.sh
ADD common.sh common.sh
ADD analyse-database.sh analyse-database.sh

FROM golang:1.20-alpine as builder
WORKDIR /app
COPY sleep_mode sleep_mode
RUN cd sleep_mode && go build -o environment_status main.go

FROM alpine:3.19

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

COPY --from=builder /app/sleep_mode/environment_status .
RUN chmod +x environment_status

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

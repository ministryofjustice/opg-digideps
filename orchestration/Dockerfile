# -------------------------
# 1) Go builder
# -------------------------
FROM golang:1.27.3-alpine3.24@sha256:d530cf60723abf1ffae0cd16bb546b379ec0550d2f3815e5b16ed69dbc00fba4 AS gobuilder

COPY sleep_mode /app/sleep_mode
WORKDIR /app/sleep_mode
RUN go build -o environment_status main.go

COPY anonymisation /app/anonymisation
WORKDIR /app/anonymisation
RUN go build -o anonymise main.go

FROM gobuilder AS tests
WORKDIR /app/anonymisation
COPY anonymisation /app/anonymisation
CMD ["go", "test", "./..."]

# -----------------------------------------
# 2) Node deps builder: run npm ci
# -----------------------------------------
FROM node:26.4.0-alpine@sha256:725aeba2364a9b16beae49e180d83bd597dbd0b15c47f1f28875c290bfd255b9 AS nodebuilder

WORKDIR /tests
COPY tests/package.json tests/package-lock.json ./

# Install deps exactly as locked
RUN npm ci

# Clean up npm cache to keep layer small
RUN rm -rf /root/.npm /root/.cach



# -----------------------------------------
# 3) Final runtime image: NO npm installed
# -----------------------------------------
FROM alpine:3.24.3@sha256:b3413572228e34bd1c9245f5565e6cd127005655e8a7899aef33d8e83255c9e6

RUN apk update && apk upgrade

ENV POSTGRES_DATABASE=**None**
ENV POSTGRES_HOST=**None**
ENV POSTGRES_PORT=5432
ENV POSTGRES_USER=**None**
ENV POSTGRES_PASSWORD=**None**
ENV POSTGRES_EXTRA_OPTS=''
ENV S3_BUCKET=**None**
ENV S3_ENDPOINT=**None**
ENV S3_PREFIX=**None**
ENV S3_S3V4=yes

# Install DB, Python tooling, AWS CLI
RUN apk add --no-cache --update \
    python3 \
    py3-pip \
    aws-cli \
    wget

# Install node runtime ONLY (no npm)
RUN apk add --no-cache --update nodejs

# Use 14 as later versions can cause issues with our backup and restore
RUN apk add --no-cache \
    --repository http://dl-cdn.alpinelinux.org/alpine/v3.17/main \
    postgresql14-client=14.15-r0

RUN mkdir certs && chmod 755 certs \
 && wget -O certs/eu-west-1-bundle.pem https://truststore.pki.rds.amazonaws.com/eu-west-1/eu-west-1-bundle.pem

COPY backup.sh /backup.sh
COPY restore.sh /restore.sh
COPY common.sh /common.sh
COPY analyse-database.sh /analyse-database.sh

# Go binaries
COPY --from=gobuilder /app/sleep_mode/environment_status /
RUN chmod +x /environment_status

COPY --from=gobuilder /app/anonymisation /anonymisation/.
RUN chmod +x /anonymisation/anonymise

# Test scripts + sources
COPY tests/run-smoke-tests.sh /tests/run-smoke-tests.sh
COPY tests/run-resilience-tests.sh /tests/run-resilience-tests.sh

COPY tests/smoke-tests/AdminSmokeTest.js /tests/smoke-tests/AdminSmokeTest.js
COPY tests/smoke-tests/FrontSmokeTest.js /tests/smoke-tests/FrontSmokeTest.js

COPY tests/resilience-tests/FrontLoadTest.js /tests/resilience-tests/FrontLoadTest.js
COPY tests/resilience-tests/Analyse.js /tests/resilience-tests/Analyse.js
COPY tests/resilience-tests/RunExperiments.js /tests/resilience-tests/RunExperiments.js

COPY tests/utility/Utility.js /tests/utility/Utility.js
COPY tests/utility/DataAnalysis.js /tests/utility/DataAnalysis.js

# Bring in node_modules produced by npm ci
COPY --from=nodebuilder /tests/node_modules /tests/node_modules
COPY --from=nodebuilder /tests/package.json /tests/package.json
COPY --from=nodebuilder /tests/package-lock.json /tests/package-lock.json

# Python smoke tests
COPY tests/smoke-tests-python/smoke_tests.py /tests/smoke-tests-python/smoke_tests.py
COPY tests/smoke-tests-python/requirements.txt /tests/smoke-tests-python/requirements.txt

RUN python -m pip install \
    --no-cache-dir \
    --target /tests/smoke-tests-python \
    -r /tests/smoke-tests-python/requirements.txt

ENV PYTHONPATH="/tests/smoke-tests-python"

WORKDIR /

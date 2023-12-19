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

# install pg_dump and s3
RUN apk add --no-cache postgresql python3 py3-pip

RUN apk update && apk upgrade

RUN apk add --no-cache aws-cli

ADD backup.sh backup.sh
ADD restore.sh restore.sh
ADD common.sh common.sh
ADD analyse-database.sh analyse-database.sh

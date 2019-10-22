FROM alpine:latest

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
RUN apk add --no-cache postgresql python py2-pip

RUN pip install awscli

RUN apk del py2-pip

ADD backup.sh backup.sh
ADD restore.sh restore.sh
ADD common.sh common.sh

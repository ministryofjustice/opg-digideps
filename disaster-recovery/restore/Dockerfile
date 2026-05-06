FROM python:3.14.4-alpine3.23@sha256:dd4d2bd5b53d9b25a51da13addf2be586beebd5387e289e798e4083d94ca837a

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --no-cache-dir --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./database_restore.py /database_restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database_restore.py
CMD ["python3", "database_restore.py", "--help"]

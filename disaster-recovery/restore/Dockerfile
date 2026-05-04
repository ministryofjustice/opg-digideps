FROM python:3.14-alpine3.22@sha256:355522f4e6b640e9255e6b6830ef52f32ece54df43db93f61101f48c5d4c6658

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --no-cache-dir --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./database_restore.py /database_restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database_restore.py
CMD ["python3", "database_restore.py", "--help"]

FROM python:3.12-alpine3.20

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./database_restore.py /database_restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database_restore.py
CMD ["python3", "database_restore.py", "--help"]

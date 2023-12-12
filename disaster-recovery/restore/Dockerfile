FROM python:3.12-alpine3.18

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./database-restore.py /database-restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database-restore.py
CMD ["python3", "database-restore.py", "--help"]

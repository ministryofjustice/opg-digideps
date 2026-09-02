FROM python:3.14.7-alpine3.24@sha256:c6ead215bfd31f1e433d968853b7a769989117115b728874824e6c0a27cb96fc

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --no-cache-dir --upgrade pip
RUN pip install -r /requirements.txt
COPY ./database_restore.py /database_restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database_restore.py
CMD ["python3", "database_restore.py", "--help"]

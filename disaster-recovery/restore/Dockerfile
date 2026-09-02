FROM python:3.14.6-alpine3.23@sha256:b165067c5afc37fa5608a3c05609cc3d51aafd808a30fbfd822ee594fef55ad4

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --no-cache-dir --upgrade pip
RUN pip install -r /requirements.txt
COPY ./database_restore.py /database_restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 database_restore.py
CMD ["python3", "database_restore.py", "--help"]

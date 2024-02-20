FROM python:3.12-alpine3.18

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross-account-backup.py /cross-account-backup.py
RUN chmod 755 cross-account-backup.py
CMD ["python3", "cross-account-backup.py"]

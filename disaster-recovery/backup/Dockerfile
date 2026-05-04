FROM python:3.14-alpine3.22@sha256:355522f4e6b640e9255e6b6830ef52f32ece54df43db93f61101f48c5d4c6658

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade
RUN pip install --no-cache-dir --upgrade pip
RUN pip install --no-cache-dir -r /requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 /cross_account_backup.py
CMD ["python3", "cross_account_backup.py"]

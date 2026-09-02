FROM python:3.14.6-alpine3.23@sha256:b165067c5afc37fa5608a3c05609cc3d51aafd808a30fbfd822ee594fef55ad4

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade
RUN pip install --no-cache-dir --upgrade pip
RUN pip install --no-cache-dir -r /requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 /cross_account_backup.py
CMD ["python3", "cross_account_backup.py"]

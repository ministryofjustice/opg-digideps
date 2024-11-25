FROM python:3.13-alpine3.19

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade
RUN pip install --upgrade pip
RUN pip install -r /requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 cross_account_backup.py
CMD ["python3", "cross_account_backup.py"]

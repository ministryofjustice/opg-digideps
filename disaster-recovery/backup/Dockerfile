FROM python:3.12-alpine3.22@sha256:f6973b8f9395204414a7f25d99a50ba1c7306064771d11a8c2a848e9af3697a6

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade
RUN pip install --no-cache-dir --upgrade pip
RUN pip install --no-cache-dir -r /requirements.txt
RUN pip uninstall -y wheel setuptools
COPY ./cross_account_backup.py /cross_account_backup.py
RUN chmod 755 /cross_account_backup.py
CMD ["python3", "cross_account_backup.py"]

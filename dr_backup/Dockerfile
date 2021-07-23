FROM python:3.10-rc-alpine3.14

RUN apk update
RUN apk upgrade
RUN pip install boto3
COPY ./dr_backup.py /dr_backup.py
RUN chmod 755 dr_backup.py
CMD ["python3", "dr_backup.py"]

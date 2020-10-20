FROM python:3.8-slim
RUN apt-get update
RUN apt-get -y upgrade
RUN pip3 install boto3
COPY ./dr_backup.py /dr_backup.py
RUN chmod 755 dr_backup.py
CMD ["python3", "dr_backup.py"]

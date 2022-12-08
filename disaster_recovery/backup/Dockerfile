FROM python:3.11-alpine3.16

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
RUN pip uninstall -y wheel
COPY ./dr_backup.py /dr_backup.py
RUN chmod 755 dr_backup.py
CMD ["python3", "dr_backup.py"]

FROM python:3.11-alpine3.18

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./dr-restore.py /dr-restore.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 dr-restore.py
CMD ["python3", "dr-restore.py", "--help"]

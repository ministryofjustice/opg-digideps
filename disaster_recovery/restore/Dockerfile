FROM python:3.11-alpine3.16

COPY requirements.txt /requirements.txt
RUN apk update && apk upgrade && pip install --upgrade pip \
    \ pip install -r /requirements.txt
COPY ./disaster_recovery.py /disaster_recovery.py
RUN pip uninstall -y wheel setuptools
RUN chmod 755 disaster_recovery.py
CMD ["python3", "disaster_recovery.py", "--help"]

FROM python:3.10-alpine3.16

RUN apk update && apk upgrade && pip3 install boto3 click
COPY ./disaster_recovery.py /disaster_recovery.py
RUN chmod 755 disaster_recovery.py
CMD ["python3", "disaster_recovery.py", "--help"]

FROM python:3-alpine

# Download and install wkhtmltopdf and bash (because executor can't use 'sh')
RUN apk --update add wkhtmltopdf bash

# Install dependencies for running web service
RUN pip install werkzeug executor gunicorn

ADD app.py /app.py
EXPOSE 80

ENTRYPOINT ["usr/local/bin/gunicorn"]

# Show the extended help
CMD ["-b", "0.0.0.0:80", "--log-file", "-", "app:application"]

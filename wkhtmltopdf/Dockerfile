FROM python:3.10-rc-alpine3.14

RUN apk update
RUN apk add bash wkhtmltopdf curl xvfb fontconfig freetype ttf-dejavu ttf-droid ttf-freefont ttf-liberation
RUN apk upgrade

# Install dependencies for running web service
RUN pip install werkzeug==1.0.1 executor==23.2 gunicorn==20.0.4

ADD docker-entrypoint.sh /docker-entrypoint.sh
ADD app.py /app.py
ADD clean-tmp /etc/periodic/hourly/clean-tmp
EXPOSE 80

# Make commands executable
RUN ["chmod", "+x", "/etc/periodic/hourly/clean-tmp"]
RUN ["chmod", "+x", "/docker-entrypoint.sh"]

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:80", "--log-file", "-", "app:application"]

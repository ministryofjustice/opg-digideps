FROM python:3.9-rc-slim-buster

# Download and install wkhtmltopdf and bash (because executor can't use 'sh')
RUN apt-get update \
    && apt-get -y install fontconfig libjpeg62-turbo libx11-6 libxcb1 libxext6 libxrender1 curl \
    && apt-get -y upgrade \
    && apt-get -y install cron wget xfonts-base xfonts-75dpi \
    && wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.stretch_amd64.deb \
    && dpkg -i wkhtmltox_0.12.6-1.stretch_amd64.deb

# Install dependencies for running web service
RUN pip install werkzeug==1.0.1 executor==23.2 gunicorn==20.0.4

ADD docker-entrypoint.sh /docker-entrypoint.sh
ADD app.py /app.py
ADD clean-tmp /etc/cron.hourly/clean-tmp
EXPOSE 80

# Make commands executable
RUN ["chmod", "+x", "/etc/cron.hourly/clean-tmp"]
RUN ["chmod", "+x", "/docker-entrypoint.sh"]

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:80", "--log-file", "-", "app:application"]

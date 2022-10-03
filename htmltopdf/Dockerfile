FROM python:3.10-alpine3.16

RUN apk update

ENV PYTHONUNBUFFERED 1

RUN apk --update --upgrade --no-cache add \
    cairo-dev pango-dev gdk-pixbuf bash jpeg-dev curl

RUN apk --update --upgrade --no-cache add fontconfig ttf-freefont font-noto terminus-font \
    && fc-cache -f \
    && fc-list | sort

RUN set -ex \
&& apk add --no-cache --virtual .build-deps musl-dev gcc g++ zlib-dev libffi-dev \
&& pip3 install --no-cache-dir werkzeug==1.0.1 executor==23.2 gunicorn==20.0.4 weasyprint==52.5 \
&& apk del .build-deps

ADD docker-entrypoint.sh /docker-entrypoint.sh
ADD app.py /app.py
ADD clean-tmp /etc/periodic/hourly/clean-tmp
EXPOSE 80

# Make commands executable
RUN ["chmod", "+x", "/etc/periodic/hourly/clean-tmp"]
RUN ["chmod", "+x", "/docker-entrypoint.sh"]

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:80", "--log-file", "-", "app:application"]

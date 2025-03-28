FROM python:3.12-alpine3.20
WORKDIR /
ENV PYTHONUNBUFFERED 1

RUN addgroup -S htmltopdf && adduser -S htmltopdf -G htmltopdf
RUN apk --update --upgrade --no-cache add \
    cairo-dev pango-dev gdk-pixbuf bash jpeg-dev curl libwebp tiff libcrypto3 libssl3 ncurses-libs ncurses-terminfo-base libwebp


RUN apk --update --upgrade --no-cache add fontconfig ttf-freefont font-noto terminus-font \
    && fc-cache -f \
    && fc-list | sort

COPY htmltopdf/requirements.txt .

RUN pip3 install --upgrade --no-cache-dir pip && pip install --upgrade --no-cache-dir pip

RUN set -ex \
&& apk add --no-cache --virtual .build-deps musl-dev gcc g++ zlib-dev libffi-dev \
&& pip3 install --no-cache-dir -r requirements.txt \
&& apk del .build-deps

COPY --chown=htmltopdf:htmltopdf htmltopdf/docker-entrypoint.sh /docker-entrypoint.sh
COPY --chown=htmltopdf:htmltopdf htmltopdf/app.py /app.py
COPY --chown=htmltopdf:htmltopdf htmltopdf/clean-tmp /etc/periodic/hourly/clean-tmp

COPY client/resources/public/assets/*/stylesheets/formatted-report.css ./tmp/
COPY client/resources/public/assets/*/stylesheets/fonts.css ./tmp/

EXPOSE 80

# Make commands executable
RUN ["chmod", "+x", "/etc/periodic/hourly/clean-tmp"]
RUN ["chmod", "+x", "/docker-entrypoint.sh"]

COPY scripts/hardening/harden.sh /harden.sh
RUN /harden.sh htmltopdf && rm /harden.sh

USER htmltopdf

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:8080", "--log-file", "-", "app:application"]

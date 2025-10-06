FROM python:3.12-alpine3.22

WORKDIR /
ENV PYTHONUNBUFFERED=1

# Create non-root user
RUN addgroup -S htmltopdf && adduser -S htmltopdf -G htmltopdf

# Install runtime dependencies
RUN apk --update --upgrade --no-cache add \
    cairo-dev pango-dev gdk-pixbuf bash jpeg-dev curl libwebp tiff libcrypto3 libssl3 ncurses-libs ncurses-terminfo-base libwebp

# Fonts
RUN apk --update --upgrade --no-cache add fontconfig ttf-freefont font-noto terminus-font \
    && fc-cache -f \
    && fc-list | sort

# Copy and install Python dependencies
COPY htmltopdf/requirements.txt .

RUN set -ex \
    && apk add --no-cache --virtual .build-deps musl-dev gcc g++ zlib-dev libffi-dev \
    && pip install --upgrade --no-cache-dir pip \
    && pip install --no-cache-dir -r requirements.txt \
    && apk del .build-deps \
    # Remove pip, setuptools, and wheel to harden the image
    && python -m ensurepip --upgrade \
    && pip uninstall -y pip setuptools wheel || true \
    && rm -rf /root/.cache /usr/lib/python*/ensurepip /usr/share/man /tmp/* /var/cache/apk/*

# Copy app and entrypoint
COPY --chown=htmltopdf:htmltopdf htmltopdf/docker-entrypoint.sh /docker-entrypoint.sh
COPY --chown=htmltopdf:htmltopdf htmltopdf/app.py /app.py
COPY --chown=htmltopdf:htmltopdf htmltopdf/clean-tmp /etc/periodic/hourly/clean-tmp

# Static assets
COPY client/resources/public/assets/*/stylesheets/formatted-report.css ./tmp/
COPY client/resources/public/assets/*/stylesheets/fonts.css ./tmp/

EXPOSE 80

COPY scripts/hardening/harden.sh /harden.sh
# Permissions and hardening
RUN chmod +x /etc/periodic/hourly/clean-tmp /docker-entrypoint.sh \
    && /harden.sh htmltopdf && rm /harden.sh

USER htmltopdf

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:8080", "--log-file", "-", "app:application"]

# Manually check PDFs generate when updating this
FROM python:3.13.15-alpine3.24@sha256:540c7d91f98ff6880174c40e99067bf5941eb54d818a7a5e094d188b196a934d

WORKDIR /
ENV PYTHONUNBUFFERED=1

# Create non-root user
RUN addgroup -S htmltopdf && adduser -S htmltopdf -G htmltopdf

# Install runtime dependencies
RUN apk add --no-cache \
    cairo \
    pango \
    gdk-pixbuf \
    jpeg \
    libwebp \
    tiff \
    libcrypto3 \
    libssl3

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
# NOTE: the resources container has to be built first for these CSS files to exist
COPY client/resources/public/assets/*/stylesheets/formatted-report.css ./tmp/
COPY client/resources/public/assets/*/stylesheets/fonts.css ./tmp/

# Check processes script
COPY --chown=htmltopdf:htmltopdf scripts/process_checks/check_processes.sh /check_processes.sh

EXPOSE 80

COPY scripts/hardening/harden.sh /harden.sh

# Permissions and hardening
RUN chmod +x /etc/periodic/hourly/clean-tmp /docker-entrypoint.sh \
    && /harden.sh htmltopdf && rm /harden.sh

USER htmltopdf

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:8080", "--log-file", "-", "app:application"]

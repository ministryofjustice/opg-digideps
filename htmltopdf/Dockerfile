FROM python:3.13.15-alpine3.24@sha256:540c7d91f98ff6880174c40e99067bf5941eb54d818a7a5e094d188b196a934d

WORKDIR /

ENV PYTHONUNBUFFERED=1

RUN addgroup -S htmltopdf \
 && adduser -S htmltopdf -G htmltopdf

# Install curl for healthcheck, requirements for pdf generation and fonts
RUN apk add --no-cache \
    curl \
    cairo \
    pango \
    gdk-pixbuf \
    jpeg \
    libwebp \
    tiff \
    fontconfig \
    ttf-freefont \
    font-noto \
    terminus-font \
    && fc-cache -f

COPY htmltopdf/requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

# Copy app and entrypoint
COPY --chown=htmltopdf:htmltopdf htmltopdf/docker-entrypoint.sh /docker-entrypoint.sh
COPY --chown=htmltopdf:htmltopdf htmltopdf/app.py /app.py
COPY --chown=htmltopdf:htmltopdf htmltopdf/clean-tmp /etc/periodic/hourly/clean-tmp

# Static assets
# NOTE: the resources container has to be built first for these CSS files to exist
COPY --chown=htmltopdf:htmltopdf client/resources/public/assets/*/stylesheets/formatted-report.css ./tmp/
COPY --chown=htmltopdf:htmltopdf client/resources/public/assets/*/stylesheets/fonts.css ./tmp/

# Check processes script
COPY --chown=htmltopdf:htmltopdf scripts/process_checks/check_processes.sh /check_processes.sh

COPY scripts/hardening/harden.sh /harden.sh

# Permissions and hardening
RUN chmod +x /etc/periodic/hourly/clean-tmp /docker-entrypoint.sh \
    && /harden.sh htmltopdf && rm /harden.sh

USER htmltopdf

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["-b", "0.0.0.0:8080", "--log-file", "-", "app:application"]

##############################
# Stage 1: Base image
##############################
FROM python:3.13.15-alpine3.24@sha256:540c7d91f98ff6880174c40e99067bf5941eb54d818a7a5e094d188b196a934d as base

WORKDIR /

##############################
# Stage X: Upgrade pinned apk packages (used only as target for pinned upgrades)
##############################
FROM base AS upgrade-pinned-apk-packages
USER root
COPY htmltopdf/Dockerfile .
COPY scripts/upgrade_apk/check-apk-ver.sh .

CMD sh -c "./check-apk-ver.sh"

##############################
# Stage 2: Runtime image
##############################
FROM base AS runtime

CMD sh -c "./check-apk-ver.sh"

ENV PYTHONUNBUFFERED=1

RUN addgroup -S htmltopdf \
 && adduser -S htmltopdf -G htmltopdf

# Install curl for healthcheck, requirements for pdf generation and fonts
RUN apk add --no-cache \
    "curl==8.22.0-r0" \
    "cairo==1.18.4-r1" \
    "pango==1.57.1-r0" \
    "gdk-pixbuf==2.44.7-r1" \
    "jpeg==9f-r0" \
    "libwebp==1.6.0-r0" \
    "tiff==4.7.1-r0" \
    "fontconfig==2.17.1-r1" \
    "ttf-freefont==20120503-r4" \
    "font-noto==2026.06.01-r0" \
    "terminus-font==4.49.1-r4" \
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

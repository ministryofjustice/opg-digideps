FROM nginxinc/nginx-unprivileged:stable-alpine

ARG PLATFORM=${PLATFORM:-amd64}
ARG APP_HOST=${APP_HOST:-127.0.0.1}
ARG APP_PORT=${APP_PORT:-9000}

USER root

WORKDIR /www/data

# Install dependencies
RUN apk --no-cache upgrade
RUN apk --no-cache add wget libcap

COPY --chown=nginx client/resources/public/ /www/data/public/
COPY --chown=nginx client/docker/web/config/default.conf /etc/nginx/conf.d/default.conf.tmpl
COPY --chown=nginx client/docker/web/config/nginx.conf /etc/nginx/nginx.conf

RUN envsubst '$APP_HOST $APP_PORT' < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

# Enable nginx to listen on privileged ports
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && apk del libcap

# Copy and secure health-check script
RUN mkdir /opt/scripts
COPY client/docker/web/health-check.sh /opt/scripts/health-check.sh
RUN chown nginx:nginx /opt/scripts/health-check.sh && chmod 550 /opt/scripts/health-check.sh

# Harden nginx security
COPY scripts/hardening/harden-nginx.sh /opt/scripts/harden-nginx.sh
RUN /opt/scripts/harden-nginx.sh && rm /opt/scripts/harden-nginx.sh

#Check processes script
COPY --chown=nginx scripts/process_checks/check_processes.sh /www/data/check_processes.sh

USER nginx


CMD ["sh", "-lc", "\
  set -e; \
  # Start a background probe; it will stop once endpoints return 200 \
  ( \
    for i in $(seq 1 30); do \
      code1=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/health-check || true); \
      code2=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/health-check/service || true); \
      if [ \"$code1\" = 200 ] && [ \"$code2\" = 200 ]; then \
        echo \"health checks OK (200/200)\"; \
        break; \
      fi; \
      echo \"waiting for app: /health-check=$code1, /health-check/service=$code2\"; \
      sleep 1; \
    done \
  ) & \
  # Now run Nginx in the foreground \
  exec nginx -g 'daemon off;' \
"]

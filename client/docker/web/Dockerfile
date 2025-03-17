FROM nginxinc/nginx-unprivileged:stable-alpine

ARG PLATFORM=${PLATFORM:-amd64}
ARG APP_HOST=${APP_HOST:-127.0.0.1}
ARG APP_PORT=${APP_PORT:-9000}
ARG SERVICE=${SERVICE:-client}

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

USER nginx

# Start services
CMD ["sh", "-c", "nginx -g 'daemon off;'"]

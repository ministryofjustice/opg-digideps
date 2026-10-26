FROM nginxinc/nginx-unprivileged:stable-alpine3.24@sha256:f5116ed4606f3eb015dd4a8e28cb9b351476dc0ad4bc6e9f93b19cec130cdf03

ARG PLATFORM=${PLATFORM:-amd64}
ARG APP_HOST=${APP_HOST:-127.0.0.1}
ARG APP_PORT=${APP_PORT:-9000}

USER root

WORKDIR /www/data

# Install dependencies
RUN apk --no-cache upgrade
RUN apk --no-cache add wget libcap

COPY --chown=nginx api/docker/web/config/default.conf /etc/nginx/conf.d/default.conf.tmpl
COPY --chown=nginx api/docker/web/config/nginx.conf /etc/nginx/nginx.conf

RUN envsubst '$APP_HOST $APP_PORT' < /etc/nginx/conf.d/default.conf.tmpl > /etc/nginx/conf.d/default.conf

# Enable nginx to listen on privileged ports
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && apk del libcap

# Harden nginx security
COPY scripts/hardening/harden-nginx.sh /opt/scripts/harden-nginx.sh
RUN /opt/scripts/harden-nginx.sh && rm /opt/scripts/harden-nginx.sh

#Check processes script
COPY --chown=nginx scripts/process_checks/check_processes.sh /www/data/check_processes.sh

USER nginx

# Start services
CMD sh -c "/www/data/check_processes.sh & exec nginx -g 'daemon off;'"

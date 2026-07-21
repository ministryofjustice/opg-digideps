FROM nginx:1.31.3-alpine3.24

ARG PLATFORM=${PLATFORM:-amd64}
ARG APP_HOST=${APP_HOST:-127.0.0.1}
ARG APP_PORT=${APP_PORT:-9000}

USER root

WORKDIR /www/data

# Install dependencies
RUN apk --no-cache upgrade \
    && apk --no-cache add wget

COPY client/resources/public/ /www/data/public/
COPY client/docker/web/config/default.conf /etc/nginx/conf.d/default.conf.tmpl
COPY client/docker/web/config/nginx.conf /etc/nginx/nginx.conf

RUN envsubst '$APP_HOST $APP_PORT' \
    < /etc/nginx/conf.d/default.conf.tmpl \
    > /etc/nginx/conf.d/default.conf

#
# Permissions required for running nginx as non-root
#
RUN mkdir -p \
        /var/cache/nginx \
        /tmp/nginx \
    && chown -R nginx:nginx \
        /www/data \
        /var/cache/nginx \
        /tmp/nginx \
        /etc/nginx

# Copy and secure health-check script
RUN mkdir -p /opt/scripts
COPY client/docker/web/health-check.sh /opt/scripts/health-check.sh
RUN chown nginx:nginx /opt/scripts/health-check.sh \
    && chmod 550 /opt/scripts/health-check.sh

# Harden nginx security
COPY scripts/hardening/harden-nginx.sh /opt/scripts/harden-nginx.sh
RUN /opt/scripts/harden-nginx.sh \
    && rm /opt/scripts/harden-nginx.sh

# Process check script
COPY --chown=nginx scripts/process_checks/check_processes.sh /www/data/check_processes.sh

USER nginx

CMD sh -c "/www/data/check_processes.sh & exec nginx -g 'daemon off;'"

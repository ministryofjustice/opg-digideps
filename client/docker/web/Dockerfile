FROM nginxinc/nginx-unprivileged:stable-alpine

ARG PLATFORM=${PLATFORM:-amd64}

USER root

WORKDIR /www/data

# Install dependencies
RUN apk --no-cache upgrade
RUN apk --no-cache add wget libcap

# Add Confd to configure nginx on start
ENV CONFD_VERSION="0.16.0"
ADD https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-${PLATFORM} /usr/local/bin/confd
RUN chmod +x /usr/local/bin/confd

COPY --chown=nginx client/docker/web/confd /etc/confd
COPY --chown=nginx client/resources/public/ /www/data/public/

# Enable nginx to listen on privileged ports
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && apk del libcap

# Copy and secure health-check script
RUN mkdir /opt/scripts
COPY client/docker/web/health-check.sh /opt/scripts/health-check.sh
RUN chown nginx:nginx /opt/scripts/health-check.sh \
    && chmod 550 /opt/scripts/health-check.sh \
    && chown -R nginx:nginx /etc/nginx \
    && chmod -R 755 /etc/nginx \
    && chown -R nginx:nginx /var/cache/nginx \
    && chmod -R 755 /var/cache/nginx \
    && mkdir /etc/nginx_backup && cp -r /etc/nginx/* /etc/nginx_backup

# Directory copy for EFS
COPY --chown=nginx scripts/miscellaneous/efs_directories.sh /opt/scripts/efs_directories.sh
RUN /opt/scripts/efs_directories.sh backup /etc/nginx

# Harden nginx security
COPY scripts/hardening/harden-nginx.sh /opt/scripts/harden-nginx.sh
RUN /opt/scripts/harden-nginx.sh && rm /opt/scripts/harden-nginx.sh

USER nginx

# Start services
CMD ["sh", "-c", "df -h && id -u && /opt/scripts/efs_directories.sh restore /etc/nginx && ls -alt /etc/nginx && confd -onetime -backend env && nginx -g 'daemon off;'"]

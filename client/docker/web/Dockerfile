FROM nginxinc/nginx-unprivileged:stable-alpine

USER root

WORKDIR /www/data

# Install dependencies
RUN apk --no-cache upgrade
RUN apk --no-cache add wget libcap

# Add Confd to configure nginx on start
ENV CONFD_VERSION="0.16.0"
ADD https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-arm64 /usr/local/bin/confd
RUN chmod +x /usr/local/bin/confd

# Add Waitforit to wait on app starting
ENV WAITFORIT_VERSION="v2.4.1"
ADD https://github.com/maxclaus/waitforit/releases/download/${WAITFORIT_VERSION}/waitforit-linux_amd64 /usr/local/bin/waitforit
RUN chmod +x /usr/local/bin/waitforit

COPY --chown=nginx client/docker/web/confd /etc/confd
COPY --chown=nginx client/resources/public/ /www/data/public/

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
CMD ["sh", "-c", "confd -onetime -backend env && nginx -g 'daemon off;'"]

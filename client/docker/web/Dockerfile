FROM nginxinc/nginx-unprivileged:stable-alpine

USER root

# Set a dedicated working directory instead of /
WORKDIR /app

# Upgrade system packages securely
RUN apk --no-cache upgrade

# Install dependencies
RUN apk --no-cache add wget libcap

# Add Confd to configure nginx on start
ENV CONFD_VERSION="0.16.0"
ADD https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64 /usr/local/bin/confd
RUN chmod +x /usr/local/bin/confd

# Add Waitforit to wait on app starting
ENV WAITFORIT_VERSION="v2.4.1"
ADD https://github.com/maxclaus/waitforit/releases/download/${WAITFORIT_VERSION}/waitforit-linux_amd64 /usr/local/bin/waitforit
RUN chmod +x /usr/local/bin/waitforit

# Copy configuration files with proper ownership
COPY --chown=nginx client/docker/web/confd /app/confd
COPY --chown=nginx client/resources/public/ /app/public/

# Enable nginx to listen on privileged ports
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && apk del libcap

# Copy and secure health-check script
COPY client/docker/web/health-check.sh /app/health-check.sh
RUN chown nginx:nginx /app/health-check.sh && chmod 550 /app/health-check.sh

# Harden nginx security
COPY scripts/hardening/harden-nginx.sh /app/harden-nginx.sh
RUN /app/harden-nginx.sh && rm /app/harden-nginx.sh

# Switch back to nginx user
USER nginx

# Start services
CMD ["sh", "-c", "confd -onetime -backend env && nginx -g 'daemon off;'"]

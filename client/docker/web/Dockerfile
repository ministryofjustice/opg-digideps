FROM opg-digideps-resources:latest as webpack

FROM nginxinc/nginx-unprivileged:stable-alpine
USER root

RUN apk upgrade && \
  apk update curl

# Add Confd to configure nginx on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd
#255d2559f3824dd64df059bdc533fd6b697c070db603c76aaf8d1d5e6b0cc334

# Add Waitforit to wait on app starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

COPY --chown=nginx client/docker/web/confd /etc/confd
COPY --chown=nginx --from=webpack /app/public/assets public/assets
COPY --chown=nginx --from=webpack /app/public/images public/images

RUN apk --no-cache add libcap && \
    setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx && \
    apk del libcap

COPY scripts/hardening/harden-nginx.sh /harden-nginx.sh
RUN /harden-nginx.sh && rm /harden-nginx.sh

USER nginx

CMD confd -onetime -backend env \
  && nginx -g "daemon off;"

FROM alpine:3.20

ENV CACHE_DIR=/var/spool/squid \
    LOG_DIR=/var/log/squid

RUN apk add --no-cache squid

COPY ./squid/entrypoint.sh /home/squid/entrypoint.sh
COPY ./squid/domain_allow_list.txt /etc/squid/domain_allow_list.txt
COPY ./squid/squid.conf /etc/squid/squid.conf

RUN chmod 755 /home/squid/entrypoint.sh

RUN mkdir -p ${LOG_DIR} \
  && chmod -R 755 ${LOG_DIR} \
  && chown -R squid ${LOG_DIR} \
  && mkdir -p ${CACHE_DIR} \
  && chmod -R 755 ${LOG_DIR} \
  && chown -R squid ${CACHE_DIR}

EXPOSE 3128/tcp
USER squid
ENTRYPOINT ["/home/squid/entrypoint.sh"]

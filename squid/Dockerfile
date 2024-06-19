FROM alpine:3.20

ENV CACHE_DIR=/var/spool/squid \
    LOG_DIR=/var/log/squid

RUN apk add --no-cache squid openssl curl

COPY ./squid/entrypoint.sh /home/squid/entrypoint.sh
COPY ./squid/domain_allow_list.txt /etc/squid/domain_allow_list.txt
COPY ./squid/http_domain_allow_list.txt /etc/squid/http_domain_allow_list.txt
COPY ./squid/squid.conf /etc/squid/squid.conf

RUN chown -R squid.squid /home/squid && chmod 755 /home/squid/entrypoint.sh

RUN mkdir -p ${LOG_DIR} \
  && chmod -R 755 ${LOG_DIR} \
  && chown -R squid ${LOG_DIR} \
  && mkdir -p ${CACHE_DIR} \
  && chmod -R 755 ${LOG_DIR} \
  && chown -R squid ${CACHE_DIR}

RUN /usr/lib/squid/security_file_certgen -c -s /var/spool/squid/ssl_db -M 4MB
RUN chown -R squid.squid /var/spool/squid/ssl_db

RUN mkdir /usr/local/squid && mkdir /usr/local/squid/ssl_cert
COPY ./squid/certs/myca.pem /usr/local/squid/ssl_cert/myca.pem
RUN chown -R squid.squid /usr/local/squid/ssl_cert

#RUN cd /usr/local/squid/ssl_cert && \
#    openssl req -new -newkey rsa:2048 -days 1365 -nodes -x509 \
#    -keyout myca.pem -out myca.pem \
#    -subj "/C=US/ST=California/L=SanFrancisco/O=MyOrg/OU=MyUnit/CN=mydomain.com"



EXPOSE 3128/tcp
USER squid
ENTRYPOINT ["/home/squid/entrypoint.sh"]

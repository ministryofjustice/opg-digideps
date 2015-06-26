FROM registry.service.dsd.io/opguk/php-fpm:0.1.25

RUN  apt-get update && apt-get install -y \
     php-pear php5-curl php5-memcached php5-redis \
     nodejs && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

# application
ADD  . /app
RUN  chown -R app /app

USER app
ENV  HOME /app

WORKDIR /app
RUN  chmod a+x phing.phar
RUN  ./phing.phar buildonly

USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd

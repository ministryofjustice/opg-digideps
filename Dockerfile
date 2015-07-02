FROM registry.service.dsd.io/opguk/php-fpm:0.1.25

RUN  apt-get update && apt-get install -y \
     php-pear php5-curl php5-memcached php5-redis php5-pgsql \
     nodejs && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# build app dependencies
COPY composer.json /app/
COPY composer.lock /app/
RUN  chown -R app /app
WORKDIR /app
USER app
ENV  HOME /app
RUN  composer install --prefer-source --no-interaction --no-scripts

# install remaining parts of app
ADD  . /app
USER root
RUN  chown -R app /app
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction

# cleanup
RUN  rm /app/app/config/parameters.yml
USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd
ADD docker/nginx/app.conf /etc/nginx/conf.d/app.conf

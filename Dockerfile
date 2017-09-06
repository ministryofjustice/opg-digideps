FROM registry.service.opg.digital/opguk/php-fpm

# adds nodejs pkg repository
RUN  curl --silent --location https://deb.nodesource.com/setup_4.x | bash -

RUN  apt-add-repository ppa:brightbox/ruby-ng && \
        apt-get update && \
        apt-get install -y \
        php-pear php5-curl php5-memcached php5-redis \
        dos2unix postgresql-client \
        nodejs ruby2.4 ruby2.4-dev && \
        apt-get clean && apt-get autoremove && \
        rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

#upgrade npm
RUN  npm install npm@4.6.1 -g
RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN  npm install --global gulp
RUN  npm install --global browserify
RUN  gem install --no-ri --no-rdoc sass -v 3.4.25
RUN  gem install --no-ri --no-rdoc scss_lint -v 0.54.0

# build app dependencies
COPY composer.json /app/
COPY composer.lock /app/
WORKDIR /app
USER app
ENV  HOME /app
RUN  composer install --prefer-source --no-interaction --no-scripts
RUN  composer dump-autoload --optimize
COPY package.json /app/
RUN  npm install

# install remaining parts of app
ADD  . /app
USER root
RUN find . -not -user app -exec chown app:app {} \;
# crontab
COPY scripts/cron/digideps /etc/cron.d/digideps
RUN chmod 0744 /etc/cron.d/digideps
# post-install scripts
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction
RUN  NODE_ENV=production gulp

# cleanup
RUN  rm /app/app/config/parameters.yml
USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd

# let's make sure they always work
RUN dos2unix /app/scripts/*

# copy init scripts
ADD  docker/my_init.d /etc/my_init.d
RUN  chmod a+x /etc/my_init.d/*

ENV  OPG_SERVICE client
ENV  OPG_DOCKER_TAG 0.0.0

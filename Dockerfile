FROM registry.service.opg.digital/opguk/php-fpm:0.1.216

# adds nodejs pkg repository
RUN  curl --silent --location https://deb.nodesource.com/setup_4.x | bash -

# Install rvm, ruby, bundler
RUN curl -sSL https://get.rvm.io | grep -v __rvm_print_headline | bash -s stable
RUN /bin/bash -l -c "rvm requirements"
RUN /bin/bash -l -c "rvm install 2.1.0"
RUN /bin/bash -l -c "gem install bundler --no-ri --no-rdoc"
RUN /bin/bash -l -c "gem install sass"
RUN /bin/bash -l -c "gem install scss_lint"

RUN  apt-get update && apt-get install -y \
     php-pear php5-curl php5-memcached php5-redis \
     dos2unix postgresql-client \
     nodejs && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

#upgrade npm
RUN  npm install npm@4.6.1 -g
RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN  npm install --global gulp
RUN  npm install --global browserify
#RUN  gem install sass
#RUN  gem install scss_lint

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
USER app
ENV  HOME /app
#do we still need the post-install-cmd
RUN  composer run-script post-install-cmd --no-interaction
RUN  NODE_ENV=production gulp

#TODO chose position of this
RUN sass --load-path /app/vendor/alphagov/govuk_frontend_toolkit/stylesheets /app/src/AppBundle/Resources/assets/scss/formatted-report.scss /app/src/AppBundle/Resources/views/Css/formatted-report.html.twig


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

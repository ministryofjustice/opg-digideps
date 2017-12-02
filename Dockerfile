FROM registry.service.opg.digital/opguk/digi-deps-api-base:nightly

# build app dependencies
WORKDIR /app
USER app
ENV  HOME /app
COPY composer.json /app/
COPY composer.lock /app/
RUN  composer install --prefer-dist --no-interaction --no-scripts

# install remaining parts of app
ADD  . /app
USER root
RUN find . -not -user app -exec chown app:app {} \;
# crontab
COPY scripts/cron/digideps /etc/cron.d/digideps
RUN chmod 0744 /etc/cron.d/digideps
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction
RUN  composer dump-autoload --optimize

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

ENV  OPG_SERVICE api


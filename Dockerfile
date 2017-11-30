FROM registry.service.opg.digital/opguk/digi-deps-frontend-base:nightly

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

# remove parameters.yml (will be regenerated at startup time from docker)
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

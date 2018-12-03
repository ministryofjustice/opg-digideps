FROM registry.service.opg.digital/opguk/digi-deps-frontend-base:nightly

# INSTALL postgresql-client-9.6, neede to connect to postgres 9.6 db from test container
# https://askubuntu.com/questions/831292/how-do-i-install-postgresql-9-6-on-any-ubuntu-version
USER root
RUN add-apt-repository "deb http://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main"
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
RUN apt-get update && \
    apt-get install -y postgresql-client-9.6 && \
    apt-get clean && apt-get autoremove && \
    rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

WORKDIR /app
USER app
ENV  HOME /app
COPY composer.json /app/
COPY composer.lock /app/
RUN  composer install --prefer-dist --no-interaction --no-scripts
COPY package.json /app/
COPY package-lock.json /app/
RUN  npm -g set progress=false
RUN  npm install

# install remaining parts of app
ADD  . /app
USER root
RUN find . -not -user app -exec chown app:app {} \;
# crontab
COPY scripts/cron/digideps /etc/cron.d/digideps
RUN chmod 0744 /etc/cron.d/digideps

# Install version of Gulp CLI thatt works with Gulp 4
RUN npm i -g gulp-cli@2.0.1

# post-install scripts
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction
RUN  composer dump-autoload --optimize
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

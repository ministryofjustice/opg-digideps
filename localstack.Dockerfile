FROM localstack/localstack:0.12.17.5 as localstack

COPY ./docker/localstack-init.sh /docker-entrypoint-initaws.d/init.sh
RUN chmod 544 /docker-entrypoint-initaws.d/init.sh

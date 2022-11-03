FROM localstack/localstack:0.14.2 as localstack

RUN apt -yq install bash-completion bash

COPY ./client/tests/csv/paProDeputyReport.csv /tmp/paProDeputyReport.csv
COPY ./client/tests/csv/layDeputyReport.csv /tmp/layDeputyReport.csv
COPY ./docker/localstack-init.sh /docker-entrypoint-initaws.d/init.sh
RUN chmod 544 /docker-entrypoint-initaws.d/init.sh
RUN apt-get -y install jq

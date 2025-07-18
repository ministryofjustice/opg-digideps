FROM localstack/localstack:4.6.0 AS localstack

RUN apt-get -yq install bash-completion bash

COPY ./client/app/tests/csv/paProDeputyReport.csv /tmp/paProDeputyReport.csv
COPY ./client/app/tests/csv/layDeputyReport.csv /tmp/layDeputyReport.csv

COPY ./local-resources/localstack/init/init.sh /etc/localstack/init/ready.d/init.sh
RUN chmod 544 /etc/localstack/init/ready.d/init.sh

COPY ./local-resources/localstack/wait/healthcheck.sh /scripts/wait/healthcheck.sh
RUN chmod 544 /scripts/wait/healthcheck.sh

RUN apt-get -y install jq

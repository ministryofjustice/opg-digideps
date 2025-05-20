FROM localstack/localstack:0.14.5 AS localstack

RUN apt-get -yq install bash-completion bash

COPY ./client/app/tests/csv/paProDeputyReport.csv /tmp/paProDeputyReport.csv
COPY ./client/app/tests/csv/layDeputyReport.csv /tmp/layDeputyReport.csv

COPY ./local-resources/localstack/init/init.sh /docker-entrypoint-initaws.d/init.sh
COPY ./local-resources/localstack/wait /scripts/wait
RUN chmod 544 /docker-entrypoint-initaws.d/init.sh
RUN apt-get -y install jq

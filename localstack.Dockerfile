FROM localstack/localstack:0.14.2 as localstack

RUN apt -yq install bash-completion bash

COPY ./api/tests/Behat/fixtures/sirius-csvs/org-3-valid-rows.csv /tmp/paProDeputyReport.csv
COPY ./api/tests/Behat/fixtures/sirius-csvs/lay-3-valid-rows.csv /tmp/layDeputyReport.csv
COPY ./docker/localstack-init.sh /docker-entrypoint-initaws.d/init.sh
RUN chmod 544 /docker-entrypoint-initaws.d/init.sh
RUN apt-get -y install jq

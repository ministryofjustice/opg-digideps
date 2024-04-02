#!/bin/bash

set -e
set -o pipefail

echo "===== Running load test in the background against Frontend ====="

curl -kv -w '\n* Response time: %{time_total}s\n' https://ddls1011560.complete-deputy-report.service.gov.uk/health-check/service
curl -kv -w '\n* Response time: %{time_total}s\n' https://ddls1011560.complete-deputy-report.service.gov.uk/health-check/service

node tests/resilience-tests/FrontLoadTest.js &
# Wait for a minute so that we can have a baseline
sleep 360
#if [[ ${ENVIRONMENT} != "local" ]]
#then
#    node tests/resilience-tests/RunExperiments.js
#fi
#sleep 300
#cat tests/resilience-tests/task_timings.csv
node tests/resilience-tests/Analyse.js
echo "===== Experiment tests completed without any issues ====="

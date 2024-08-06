#!/bin/bash

set -e
set -o pipefail

sleep 30
echo "===== Running load test in the background against Frontend ====="
node tests/resilience-tests/FrontLoadTest.js &
# Wait for a minute so that we can have a baseline
sleep 60
if [[ ${ENVIRONMENT} != "local" ]]
then
    node tests/resilience-tests/RunExperiments.js
fi
sleep 300
node tests/resilience-tests/Analyse.js
echo "===== Output of Error File ====="
cat ${TASK_ERROR_LOG}
echo "===== Experiment tests completed without any issues ====="

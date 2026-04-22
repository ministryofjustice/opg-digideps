#!/bin/bash

CLUSTER="${1}" # The Name of the ECS cluster to check services in (e.g. "DDLS-6767")
TIMEOUT_MINS="${2}" # The number of minutes to wait before timing out (e.g. "15")
shift 2
SERVICES=("$@") # The list of service names to check

POLL_INTERVAL=15 # seconds between each check
DEADLINE=$(( $(date +%s) + TIMEOUT_MINS * 60 )) # calculate the deadline timestamp from the current time and timeout

echo "========================================"
echo "Cluster:  ${CLUSTER}"
echo "Services: ${SERVICES[*]}"
echo "Timeout:  ${TIMEOUT_MINS} minutes"
echo "========================================"
echo ""

while true; do
  NOW=$(date +%s)
  if [ "${NOW}" -ge "${DEADLINE}" ]; then
    echo "Timed out after ${TIMEOUT_MINS} minutes waiting for services to stabilise. Check the ECS in the console for errors / logs."
    exit 1
  fi

  ALL_STABLE=true

  for SERVICE in "${SERVICES[@]}"; do
    # Get the current status of the service, including desired count, running count, and deployment states
    RESULT=$(aws ecs describe-services --cluster "${CLUSTER}" --services "${SERVICE}" --query 'services[0].{status:status,desired:desiredCount,running:runningCount,deployments:deployments}' --output json 2>&1)

    # service has not been created yet, or is in a failed state
    if echo "${RESULT}" | grep -q '"status": null'; then
      ALL_STABLE=false
      continue
    fi

    # The service is active, but check if any deployments have entered a FAILED state!
    FAILED=$(echo "${RESULT}" | jq -r '.deployments[] | select(.rolloutState == "FAILED") | .rolloutState' 2>/dev/null || true)
    if [ -n "${FAILED}" ]; then
      echo "${SERVICE}: deployment entered FAILED state"
      echo ""
      echo "Last 5 events:"
      # Get the last 5 events for the service:
      aws ecs describe-services --cluster "${CLUSTER}" --services "${SERVICE}" --query 'services[0].events[:5]' --output table
      exit 1
    fi

    IN_PROGRESS=$(echo "${RESULT}" | jq -r '.deployments[] | select(.rolloutState == "IN_PROGRESS") | .rolloutState' 2>/dev/null || true)
    DESIRED=$(echo "${RESULT}" | jq -r '.desired')
    RUNNING=$(echo "${RESULT}" | jq -r '.running')

    if [ -n "${IN_PROGRESS}" ] || [ "${RUNNING}" != "${DESIRED}" ]; then
      echo "${SERVICE}: running=${RUNNING}/${DESIRED}, rollout IN_PROGRESS"
      ALL_STABLE=false
    else
      echo "${SERVICE}: running=${RUNNING}/${DESIRED}, stable"
    fi
  done

  if [ "${ALL_STABLE}" = "true" ]; then
    echo ""
    echo "All services stable"
    exit 0
  fi

  REMAINING=$(( (DEADLINE - $(date +%s)) / 60 ))
  echo "--- ${REMAINING} minutes remaining, sleeping ${POLL_INTERVAL}s ---"
  echo ""
  sleep "${POLL_INTERVAL}"
done

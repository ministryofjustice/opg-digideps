#!/bin/bash

CLUSTER="${1}"
TIMEOUT_MINS="${2}"
shift 2
SERVICES=("$@")

POLL_INTERVAL=15
DEADLINE=$(( $(date +%s) + TIMEOUT_MINS * 60 ))

echo "========================================"
echo "Cluster:  ${CLUSTER}"
echo "Services: ${SERVICES[*]}"
echo "Timeout:  ${TIMEOUT_MINS} minutes"
echo "========================================"
echo ""

while true; do
  NOW=$(date +%s)
  if [ "${NOW}" -ge "${DEADLINE}" ]; then
    echo "Timed out after ${TIMEOUT_MINS} minutes waiting for services to stabilise."
    exit 1
  fi

  ALL_STABLE=true

  for SERVICE in "${SERVICES[@]}"; do
    if ! RESULT="$(aws ecs describe-services \
      --cluster "${CLUSTER}" \
      --services "${SERVICE}" \
      --output json 2>/tmp/ecs_wait_err.log)"; then
      echo "${SERVICE}: aws ecs describe-services failed"
      cat /tmp/ecs_wait_err.log
      ALL_STABLE=false
      continue
    fi

    if ! echo "${RESULT}" | jq -e . >/dev/null 2>&1; then
      echo "${SERVICE}: invalid JSON returned from aws cli"
      echo "${RESULT}"
      ALL_STABLE=false
      continue
    fi

    FAILURE_REASON="$(echo "${RESULT}" | jq -r '.failures[0].reason // empty')"
    if [ -n "${FAILURE_REASON}" ]; then
      echo "${SERVICE}: not ready or not found (${FAILURE_REASON})"
      ALL_STABLE=false
      continue
    fi

    STATUS="$(echo "${RESULT}" | jq -r '.services[0].status // empty')"
    DESIRED="$(echo "${RESULT}" | jq -r '.services[0].desiredCount // empty')"
    RUNNING="$(echo "${RESULT}" | jq -r '.services[0].runningCount // empty')"
    PRIMARY_ROLLOUT="$(echo "${RESULT}" | jq -r '.services[0].deployments[]? | select(.status == "PRIMARY") | .rolloutState // empty')"
    FAILED="$(echo "${RESULT}" | jq -r '.services[0].deployments[]? | select(.rolloutState == "FAILED") | .rolloutState' || true)"
    IN_PROGRESS_COUNT="$(echo "${RESULT}" | jq -r '[.services[0].deployments[]? | select(.rolloutState == "IN_PROGRESS")] | length')"

    if [ -z "${STATUS}" ] || [ -z "${DESIRED}" ] || [ -z "${RUNNING}" ]; then
      echo "${SERVICE}: missing expected ECS fields"
      ALL_STABLE=false
      continue
    fi

    if [ "${STATUS}" != "ACTIVE" ]; then
      echo "${SERVICE}: status=${STATUS}, waiting"
      ALL_STABLE=false
      continue
    fi

    if [ -n "${FAILED}" ]; then
      echo "${SERVICE}: deployment entered FAILED state"
      echo ""
      echo "Last 5 events:"
      aws ecs describe-services \
        --cluster "${CLUSTER}" \
        --services "${SERVICE}" \
        --query 'services[0].events[:5]' \
        --output table || true
      exit 1
    fi

    if [ "${RUNNING}" != "${DESIRED}" ] || [ "${PRIMARY_ROLLOUT}" != "COMPLETED" ] || [ "${IN_PROGRESS_COUNT}" != "0" ]; then
      echo "${SERVICE}: running=${RUNNING}/${DESIRED}, primary=${PRIMARY_ROLLOUT:-unknown}, waiting"
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

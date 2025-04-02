#!/bin/sh

echo "RUNNING HEALTHCHECK"

# Define the health check URLs
HEALTH_CHECK_URL="http://127.0.0.1:80/health-check"
SERVICE_HEALTH_CHECK_URL="http://127.0.0.1:80/health-check/service"
DEPENDENCIES_HEALTH_CHECK_URL="http://127.0.0.1:80/health-check/dependencies"

# Define the interval between dependency/service checks (in seconds)
# 5 minutes = 300 seconds
INTERVAL=300

# Temp file to store the last check time
LAST_RUN_FILE="/tmp/last_healthcheck_run"

# Function to perform a health check
check_health() {
    local url=$1
    echo "Checking health at $url"
    curl -fsS $url >/dev/null
    return $?
}

# Run the primary health check
check_health $HEALTH_CHECK_URL
if [ $? -ne 0 ]; then
    echo "Primary health check failed. Exiting with status 1."
    exit 1
fi

# Check if the last run was within the last 5 minutes
if [ -f "$LAST_RUN_FILE" ]; then
    last_run=$(cat "$LAST_RUN_FILE")
    current_time=$(date +%s)
    time_diff=$((current_time - last_run))

    if [ "$time_diff" -lt "$INTERVAL" ]; then
        echo "Health checks ran less than 5 minutes ago. Exiting."
        exit 0
    fi
fi

# Update the last run time to current time
date +%s > "$LAST_RUN_FILE"
echo "Running Service Health Check"

# Run secondary health checks (these are allowed to fail)
check_health $SERVICE_HEALTH_CHECK_URL
if [ $? -ne 0 ]; then
    echo "Service health check failed, but continuing..."
fi

check_health $DEPENDENCIES_HEALTH_CHECK_URL
if [ $? -ne 0 ]; then
    echo "Dependencies health check failed, but continuing..."
fi

echo "Health checks completed successfully."

exit 0

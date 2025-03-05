#!/bin/sh

# Usage function
usage() {
    echo "Usage: $0 -h <hostname> -p <port> -t <timeout>"
    exit 1
}

# Default timeout value
TIMEOUT=60

# Parse command-line arguments
while getopts "h:p:t:" opt; do
  case "$opt" in
    h) DATABASE_HOSTNAME=$OPTARG ;;
    p) DATABASE_PORT=$OPTARG ;;
    t) TIMEOUT=$OPTARG ;;
    *) usage ;;
  esac
done

# Validate inputs
if [[ -z "$DATABASE_HOSTNAME" || -z "$DATABASE_PORT" ]]; then
    echo "Error: Missing required arguments."
    usage
fi

# Start timer
START_TIME=$(date +%s)

# Wait for database to become available
while ! nc -z "$DATABASE_HOSTNAME" "$DATABASE_PORT"; do
    echo "Waiting for database at $DATABASE_HOSTNAME:$DATABASE_PORT..."
    sleep 2

    if [ $(( $(date +%s) - START_TIME )) -ge "$TIMEOUT" ]; then
        echo "Timeout reached after $TIMEOUT seconds. Database did not respond."
        exit 1
    fi
done

echo "Database is now reachable!"
exit 0

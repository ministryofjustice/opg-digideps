#!/usr/bin/env sh

while true; do
    echo "database_activity_in_progress activity=deputyships_csv"
    sleep 45
done &
heartbeat_pid=$!

sh scripts/task_run_console_command.sh "$@"
exit_code=$?

kill "$heartbeat_pid"
exit "$exit_code"

#!/bin/bash

while true; do
    status_code=$(curl -s -o /dev/null -w "%{http_code}" https://www.google.com)
    echo "Jim test HTTP Status Code: $status_code"
    sleep 30
done

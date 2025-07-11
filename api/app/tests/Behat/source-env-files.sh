#!/bin/sh
set -e

# Purpose of this script:
# Echo out a list of export statements based on env vars
# Skip env vars that are already set so as not to overwrite

if [ $# -lt 1 ]; then
  echo "Usage: $0 <env-file>" >&2
  exit 1
fi

ENV_FILE="$1"

if [ ! -f "$ENV_FILE" ]; then
  echo "File not found: $ENV_FILE" >&2
  exit 1
fi

while IFS='=' read -r key value || [ -n "$key" ]; do
  # Skip empty lines or comments
  case "$key" in
    ''|\#*) continue ;;
  esac

  # Skip keys that start with AWS_
  case "$key" in
    AWS_*) continue ;;
  esac

  # Strip possible surrounding quotes from value (because of the export syntax)
  value=$(echo "$value" | sed -e 's/^"\(.*\)"$/\1/' -e "s/^'\(.*\)'$/\1/")

  # Check if variable is already set
  eval "is_set=\${$key+x}"

  if [ -z "$is_set" ]; then
    printf 'export %s=%s\n' "$key" "$value"
  fi
done < "$ENV_FILE"

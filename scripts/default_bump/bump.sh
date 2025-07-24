#!/bin/sh

# Get the directory of the script
SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)

# Full path to the folder list file
FOLDER_FILE="$SCRIPT_DIR/folders-to-check.txt"

if [ ! -f "$FOLDER_FILE" ]; then
  echo "Folder list file '$FOLDER_FILE' not found."
  exit 1
fi

# Get the list of changed files
CHANGED_FILES=$(git diff --name-only origin/main...HEAD)

# Check if any changed file is in the listed folders
while IFS= read -r folder || [ -n "$folder" ]; do
  [ -z "$folder" ] && continue  # skip empty lines
  for file in $CHANGED_FILES; do
    case "$file" in
      "$folder"/*)
        echo "minor"
        exit 0
        ;;
    esac
  done
done < "$FOLDER_FILE"

echo "patch"

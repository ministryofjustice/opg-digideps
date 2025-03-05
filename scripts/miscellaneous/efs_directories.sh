#!/bin/sh

# Ensure script is run with 2 arguments
if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <comma_separated_directories> <backup|restore>"
    exit 1
fi

# Parse input arguments
DIRECTORIES=$1
ACTION=$2

# Convert comma-separated list into an array
IFS=',' read -r -a DIR_ARRAY <<< "$DIRECTORIES"

# Iterate through directories
for DIR in "${DIR_ARRAY[@]}"; do
    if [ -d "$DIR" ]; then
        BACKUP_DIR="${DIR}_backup"
        if [ "$ACTION" == "backup" ]; then
            echo "Backing up $DIR to $BACKUP_DIR"
            mkdir -p "$BACKUP_DIR"
            cp -a "$DIR" "$BACKUP_DIR"
        elif [ "$ACTION" == "restore" ]; then
            if [ -d "$BACKUP_DIR" ]; then
                echo "Restoring $DIR from $BACKUP_DIR"
                find "$BACKUP_DIR" -type f | while read -r BACKUP_FILE; do
                    REL_PATH="${BACKUP_FILE#$BACKUP_DIR/}"
                    TARGET_FILE="$DIR/$REL_PATH"
                    if [ -e "$TARGET_FILE" ]; then
                        echo "Skipping existing file: $TARGET_FILE"
                    else
                        echo "Restoring: $TARGET_FILE"
                        mkdir -p "$(dirname "$TARGET_FILE")"
                        cp -a "$BACKUP_FILE" "$TARGET_FILE"
                    fi
                done
            else
                echo "Backup directory $BACKUP_DIR does not exist. Skipping."
            fi
        else
            echo "Invalid action: $ACTION. Use 'backup' or 'restore'."
            exit 1
        fi
    else
        echo "Directory $DIR does not exist. Skipping."
    fi
done

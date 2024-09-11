#!/bin/sh

# Directory where SQL scripts are stored
SQL_DIR="./scripts/custom_sql_query_setup"

export PGPASSWORD="$DATABASE_PASSWORD"
#!/bin/bash

# Check if directory exists
if [ ! -d "$SQL_DIR" ]; then
    echo "Directory $SQL_DIR does not exist."
    exit 1
fi

# Find all SQL files in the directory, sort them numerically, and loop through each one
for sql_file in $(ls $SQL_DIR/*.sql | sort -V); do
    echo "Running $sql_file ..."
    psql -h "$DATABASE_HOSTNAME" -U "$DATABASE_USERNAME" -d "$DATABASE_NAME" -p "$DATABASE_PORT" -f "$sql_file"
    if [ $? -ne 0 ]; then
        echo "Error occurred while executing $sql_file. Exiting."
        exit 1
    fi
done

echo "All scripts executed successfully."

# Unset the password environment variable
unset PGPASSWORD

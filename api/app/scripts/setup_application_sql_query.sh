#!/bin/sh

# Directory where SQL scripts are stored
SQL_DIR="./scripts/application_user_setup"

# Export the database password for psql command
export PGPASSWORD="$DATABASE_PASSWORD"

# Check if directory exists
if [ ! -d "$SQL_DIR" ]; then
    echo "Directory $SQL_DIR does not exist."
    exit 1
fi

# Find all SQL files in the directory, sort them numerically, and loop through each one
for sql_file in $(ls $SQL_DIR/*.sql | sort -V); do
    echo "Running $sql_file ..."

    # Create a temporary file for the modified SQL
    temp_file=$(mktemp)

    # Check if password is empty and exit if it is!
    if [ -z "$WORKSPACE" ]; then
        echo "WORKSPACE is empty. Exiting..."
        exit 1
    fi

    sed "s/password-string/$APP_DB_PASSWORD/g" "$sql_file" > "$temp_file"

    cat $temp_file

    # Run the modified SQL file
    psql -h "$DATABASE_HOSTNAME" -U "$DATABASE_USERNAME" -d "$DATABASE_NAME" -p "$DATABASE_PORT" -f "$temp_file"

    # Check for errors
    if [ $? -ne 0 ]; then
        echo "Error occurred while executing $sql_file. Exiting."
        rm "$temp_file" # Remove the temp file if an error occurs
        exit 1
    fi

    # Remove the temporary file after successful execution
    rm "$temp_file"
done

echo "All scripts executed successfully."

# Unset the password environment variable
unset PGPASSWORD

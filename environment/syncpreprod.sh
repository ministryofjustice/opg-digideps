#!/usr/bin/env bash

# Synchronize Database
#
# pg dump schema & data from one database to another
# depends on secretsmanager for, well, secrets

# set environments
SOURCE=production02
TARGET=preprod

# check for prereqs
command -v aws >/dev/null 2>&1 || { echo "aws cli required but not installed"; exit 1; }
command -v jq >/dev/null 2>&1 || { echo "jq required but not installed, install with sudo yum install jq -y"; exit 1; }
command -v psql >/dev/null 2>&1 || { echo "psql required but not installed, install with sudo postgresql96 install jq -y"; exit 1; }

# pull secrets from secretsmanager
SOURCE_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${SOURCE}/database-password | jq -r .SecretString)
TARGET_PASSWORD=$(aws secretsmanager get-secret-value --secret-id ${TARGET}/database-password | jq -r .SecretString)

# Backup database to file
rm ${SOURCE}.tmp.sql
pg_dump --dbname=postgresql://digidepsmaster:${SOURCE_PASSWORD}@postgres.${SOURCE}.internal:5432/api --verbose --clean --schema public --file ${SOURCE}.tmp.sql

# Restore database from file
psql --dbname=postgresql://digidepsmaster:${TARGET_PASSWORD}@postgres.${TARGET}.internal:5432/api --echo-all --file=${SOURCE}.tmp.sql
rm ${SOURCE}.tmp.sql

echo "Synchronized ${TARGET} from ${SOURCE}"

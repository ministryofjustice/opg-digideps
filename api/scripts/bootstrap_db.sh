#!/bin/sh
set -e

export PGHOST=${DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${DATABASE_PASSWORD:=api}
export PGDATABASE=${DATABASE_NAME:=api}
export PGUSER=${DATABASE_USERNAME:=api}
export SSL=${DATABASE_SSL:=allow}

export IAM_USER_EXISTS=$(psql -c "select count(*) from pg_catalog.pg_user where usename = 'iamuser'")

if [ "$(echo ${IAM_USER_EXISTS} | awk -F' ' '{print $3}')" == "0" ]
then
    if [ "${DATABASE_IAM_AUTH}" == "1" ]
    then
        echo "Creating IAM user"
        psql -c "CREATE USER iamuser LOGIN;"
        psql -c "GRANT rds_iam TO iamuser;"
        psql -c "ALTER SCHEMA public OWNER TO iamuser"
        for tbl in `psql -qAt -c "select tablename from pg_tables where schemaname = 'public';"` ; do  psql -c "alter table \"$tbl\" owner to iamuser"; done
        for tbl in `psql -qAt -c "select sequence_name from information_schema.sequences where sequence_schema = 'public';"`; do  psql -c "alter sequence \"$tbl\" owner to iamuser"; done
        for tbl in `psql -qAt -c "select table_name from information_schema.views where table_schema = 'public';"`; do  psql -c "alter view \"$tbl\" owner to iamuser"; done
    fi
fi

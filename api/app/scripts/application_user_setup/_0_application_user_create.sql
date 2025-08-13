DO
$$
BEGIN

  IF NOT EXISTS (SELECT * FROM pg_user WHERE usename = 'application') THEN
     CREATE USER "application" WITH LOGIN;
  END IF;

  IF EXISTS (SELECT * FROM pg_roles WHERE rolname = 'pg_read_all_data') THEN
     GRANT pg_read_all_data TO "application";
  END IF;

  IF EXISTS (SELECT * FROM pg_roles WHERE rolname = 'pg_write_all_data') THEN
     GRANT pg_write_all_data TO "application";
  END IF;

END
$$
;

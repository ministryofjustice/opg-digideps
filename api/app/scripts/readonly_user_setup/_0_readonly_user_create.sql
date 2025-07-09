DO
$$
BEGIN

  IF NOT EXISTS (SELECT * FROM pg_user WHERE usename = 'readonly-db-iam-string-to-replace-with-local-environment') THEN
     CREATE USER "readonly-db-iam-string-to-replace-with-local-environment" WITH LOGIN;
  END IF;

  IF EXISTS (SELECT * FROM pg_roles WHERE rolname = 'rds_iam') THEN
     GRANT rds_iam TO "readonly-db-iam-string-to-replace-with-local-environment";
  END IF;

  ALTER USER "readonly-db-iam-string-to-replace-with-local-environment" SET log_statement = 'all';

END
$$
;

DO
$$
BEGIN

  IF NOT EXISTS (SELECT * FROM pg_user WHERE usename = 'readonly-db-iam-string-to-replace-with-local-environment') THEN
     CREATE USER "readonly-db-iam-string-to-replace-with-local-environment" WITH LOGIN;
  END IF;

  GRANT rds_iam TO "readonly-db-iam-string-to-replace-with-local-environment";

END
$$
;

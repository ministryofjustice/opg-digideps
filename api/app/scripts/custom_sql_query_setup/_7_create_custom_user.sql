DO
$$
BEGIN
  IF NOT EXISTS (SELECT * FROM pg_user WHERE usename = 'custom_sql_user') THEN
     CREATE USER custom_sql_user WITH PASSWORD 'string_to_replace_with_real_password';
  END IF;
END
$$
;

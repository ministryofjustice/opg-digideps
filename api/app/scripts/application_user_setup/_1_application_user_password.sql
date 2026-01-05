DO $$
DECLARE
  pw text := 'app_password_string';
BEGIN
  EXECUTE format('ALTER ROLE application WITH PASSWORD %L', pw);
END
$$;

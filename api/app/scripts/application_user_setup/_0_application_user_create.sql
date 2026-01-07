DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'application') THEN
    CREATE ROLE application LOGIN;
  END IF;
END
$$;

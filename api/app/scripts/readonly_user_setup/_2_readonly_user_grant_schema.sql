DO $$
DECLARE
    schema_name_var text;
BEGIN
    FOR schema_name_var IN
        SELECT schema_name FROM information_schema.schemata
        WHERE schema_name NOT IN ('information_schema', 'pg_catalog')  -- Skip system schemas
    LOOP
        EXECUTE format('GRANT USAGE ON SCHEMA %I TO readonly_sql_user;', schema_name_var);
    END LOOP;
END $$;

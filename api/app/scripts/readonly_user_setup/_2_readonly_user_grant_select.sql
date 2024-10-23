DO $$
DECLARE
    tbl record;
BEGIN
    FOR tbl IN
        SELECT schemaname, tablename FROM pg_tables
        WHERE schemaname NOT IN ('information_schema', 'pg_catalog')  -- Skip system schemas
    LOOP
        EXECUTE format('GRANT SELECT ON TABLE %I.%I TO readonly_sql_user;', tbl.schemaname, tbl.tablename);
    END LOOP;
END $$;

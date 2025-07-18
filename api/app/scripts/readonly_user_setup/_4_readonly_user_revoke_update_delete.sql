DO $$
DECLARE
    tbl record;
BEGIN
    FOR tbl IN
        SELECT schemaname, tablename FROM pg_tables
        WHERE schemaname NOT IN ('information_schema', 'pg_catalog')  -- Skip system schemas
    LOOP
        EXECUTE format('REVOKE UPDATE, DELETE ON TABLE %I.%I FROM "readonly-db-iam-string-to-replace-with-local-environment";', tbl.schemaname, tbl.tablename);
    END LOOP;
END $$;

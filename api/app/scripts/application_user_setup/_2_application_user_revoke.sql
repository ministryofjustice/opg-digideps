DO $$
DECLARE
    rolename text;
BEGIN
    FOR rolename IN
        SELECT unnest(ARRAY['pg_read_server_files', 'pg_write_server_files',
                            'pg_read_all_settings', 'pg_read_all_stats', 'pg_stat_scan_tables',
                            'pg_monitor', 'pg_database_owner', 'pg_signal_backend',
                            'pg_execute_server_program', 'pg_checkpoint', 'pg_maintain',
                            'pg_use_reserved_connections', 'pg_create_subscription'])
    LOOP
        IF EXISTS (
            SELECT 1
            FROM pg_auth_members m
            JOIN pg_roles r_role ON r_role.oid = m.roleid
            JOIN pg_roles r_member ON r_member.oid = m.member
            WHERE r_role.rolname = rolename
              AND r_member.rolname = 'application'
        ) THEN
            EXECUTE format('REVOKE %I FROM application', rolename);
        END IF;
    END LOOP;
END$$;

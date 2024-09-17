CREATE OR REPLACE PROCEDURE audit.revoke_custom_query(
    IN query_id INT,
    INOUT result_message TEXT
)
LANGUAGE plpgsql SECURITY DEFINER AS $$
DECLARE
    query_run_on TIMESTAMP;
BEGIN
    -- Retrieve the run_on timestamp for the given query ID
    SELECT run_on INTO query_run_on
    FROM audit.custom_queries
    WHERE id = query_id;

    -- Check if the query exists
    IF NOT FOUND THEN
        result_message := FORMAT('Error: Query with id %s does not exist', query_id);
        RETURN;
    END IF;

    -- Check if the query has already been run
    IF query_run_on IS NOT NULL THEN
        result_message := FORMAT('Error: Cannot revoke query with id %s because it has already been run on %s', query_id, query_run_on);
        RETURN;
    END IF;

    -- Delete the query
    DELETE FROM audit.custom_queries
    WHERE id = query_id;

    -- Check if the delete affected any rows (just for verification)
    IF NOT FOUND THEN
        result_message := FORMAT('Failed to delete query with id %s', query_id);
        RETURN;
    END IF;

    result_message := FORMAT('Success: Query with id %s successfully revoked', query_id);

EXCEPTION
    WHEN OTHERS THEN
        result_message := FORMAT('Error: %s', SQLERRM);
END;
$$;

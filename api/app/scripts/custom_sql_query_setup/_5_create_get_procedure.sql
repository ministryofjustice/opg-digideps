CREATE OR REPLACE PROCEDURE get_custom_query(
    IN query_id INT DEFAULT NULL,
    INOUT out_id INT DEFAULT NULL,
    INOUT out_query text DEFAULT NULL,
    INOUT out_confirmation_query text DEFAULT NULL,
    INOUT out_created_by VARCHAR DEFAULT NULL,
    INOUT out_created_on TIMESTAMP DEFAULT NULL,
    INOUT out_signed_off_by VARCHAR DEFAULT NULL,
    INOUT out_signed_off_on TIMESTAMP DEFAULT NULL,
    INOUT out_run_on TIMESTAMP DEFAULT NULL,
    INOUT out_expected_before INT DEFAULT NULL,
    INOUT out_expected_after INT DEFAULT NULL,
    INOUT out_passed BOOLEAN DEFAULT false
)
LANGUAGE plpgsql SECURITY DEFINER AS $$
BEGIN
    -- If the query_id is provided, fetch the row with the given id
    IF query_id IS NOT NULL THEN
        -- Check if the id exists
        SELECT id, query, confirmation_query, created_by, created_on,
               signed_off_by, signed_off_on, run_on, expected_before,
               expected_after, passed
        INTO out_id, out_query, out_confirmation_query, out_created_by,
             out_created_on, out_signed_off_by, out_signed_off_on,
             out_run_on, out_expected_before, out_expected_after,
             out_passed
        FROM custom_queries
        WHERE id = query_id;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'Query with id %s does not exist', query_id;
        END IF;

    -- If no id is provided, fetch the latest row based on created_on
    ELSE
        -- Check if there are any rows in the table
        PERFORM 1 FROM custom_queries;

        IF NOT FOUND THEN
            RAISE EXCEPTION 'No queries exist in the table';
        END IF;

        -- Return the latest row based on created_on
        SELECT id, query, confirmation_query, created_by, created_on,
               signed_off_by, signed_off_on, run_on, expected_before,
               expected_after, passed
        INTO out_id, out_query, out_confirmation_query, out_created_by,
             out_created_on, out_signed_off_by, out_signed_off_on,
             out_run_on, out_expected_before, out_expected_after,
             out_passed
        FROM custom_queries
        ORDER BY created_on DESC
        LIMIT 1;
    END IF;
END;
$$;

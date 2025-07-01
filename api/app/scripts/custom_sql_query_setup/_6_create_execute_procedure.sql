CREATE OR REPLACE PROCEDURE audit.execute_custom_query(
    IN query_id INT,
    INOUT result_message text DEFAULT NULL
)
LANGUAGE plpgsql SECURITY DEFINER AS $$
DECLARE
    v_query TEXT;
    v_confirmation_query TEXT;
    v_created_by VARCHAR;
    v_signed_off_by VARCHAR;
    v_run_on TIMESTAMP;
    v_expected_before INT;
    v_expected_after INT;
    v_maximum_rows_affected INT;
    v_result_before INT;
    v_result_after INT;
    v_row_count INT;
BEGIN
    -- Retrieve details for the specified query_id
    SELECT query, confirmation_query, created_by, signed_off_by, run_on,
           expected_before, expected_after, maximum_rows_affected
    INTO v_query, v_confirmation_query, v_created_by, v_signed_off_by, v_run_on,
         v_expected_before, v_expected_after, v_maximum_rows_affected
    FROM audit.custom_queries
    WHERE id = query_id;

    -- Check if the id exists
    IF NOT FOUND THEN
        result_message := FORMAT('Error: Query with id %s does not exist', query_id);
        RETURN;
    END IF;

    -- Check if signed_off_by is not null
    IF v_signed_off_by IS NULL THEN
        result_message := FORMAT('Error: Query with id %s has not been signed off yet', query_id);
        RETURN;
    END IF;

    -- Execute the confirmation_query and check the result before running the query
    EXECUTE v_confirmation_query INTO v_result_before;

    IF v_result_before != v_expected_before THEN
        result_message := FORMAT('Error: Expected result of pre execute verification query of %s does not match actual result of %s for query_id %s... ', v_expected_before, v_result_before, query_id);
        RETURN;
    END IF;

    -- Begin transaction (implicitly handled by PL/pgSQL)
    -- Set run_on to now
    UPDATE audit.custom_queries
    SET run_on = NOW()
    WHERE id = query_id;

    -- Execute the main query
    EXECUTE v_query;

    -- Capture the number of rows affected
    GET DIAGNOSTICS v_row_count = ROW_COUNT;

    -- Check if the number of affected rows exceeds threshold
    IF v_row_count > v_maximum_rows_affected THEN
        RAISE EXCEPTION 'Too many rows affected (%), threshold is %, aborting.', v_row_count, v_maximum_rows_affected;
    END IF;

    -- Re-run the confirmation_query and check the result after running the query
    EXECUTE v_confirmation_query INTO v_result_after;

    IF v_result_after != v_expected_after THEN
        -- Raise an exception to trigger a rollback
        RAISE EXCEPTION 'Error: Expected result of post execute verification query of % does not match actual result of % for query_id %... ',
            v_expected_after, v_result_after, query_id;
    END IF;

    -- Update the passed field to True if everything is successful
    UPDATE audit.custom_queries
    SET passed = TRUE
    WHERE id = query_id;

    -- Return success message
    result_message := FORMAT('Success: successfully executed query for query_id %s', query_id);

EXCEPTION
    WHEN OTHERS THEN
        -- Capture error and set result_message
        result_message := FORMAT('Error: %s', SQLERRM);
        -- Optionally re-raise the exception to ensure rollback
        RAISE;
END;
$$;

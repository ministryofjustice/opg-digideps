CREATE OR REPLACE PROCEDURE audit.execute_custom_query(
    IN query_id INT,
    INOUT result_message TEXT
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
    v_result_before INT;
    v_result_after INT;
BEGIN
    -- Retrieve details for the specified query_id
    SELECT query, confirmation_query, created_by, signed_off_by, run_on,
           expected_before, expected_after
    INTO v_query, v_confirmation_query, v_created_by, v_signed_off_by, v_run_on,
         v_expected_before, v_expected_after
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

    -- Execute the confirmation_query and check the result
    EXECUTE v_confirmation_query INTO v_result_before;

    IF v_result_before != v_expected_before THEN
        result_message := FORMAT('Error: Expected before of %s does not match actual result of %s for query_id %s', query_id, v_expected_before, v_result_before);
        RETURN;
    END IF;

    -- Set run_on to now
    UPDATE audit.custom_queries
    SET run_on = NOW()
    WHERE id = query_id;

    -- Execute the main query
    EXECUTE v_query;

    -- Re-run the confirmation_query and check the result
    EXECUTE v_confirmation_query INTO v_result_after;

    IF v_result_after != v_expected_after THEN
        result_message := FORMAT('Error: Expected after of %s does not match actual result of %s for query_id %s', query_id, v_expected_after, v_result_after);
        RETURN;
    END IF;

    -- Update the passed field to True
    UPDATE audit.custom_queries
    SET passed = TRUE
    WHERE id = query_id;

    result_message := FORMAT('Success: successfully executed query for query_id %s', query_id);
EXCEPTION
    WHEN OTHERS THEN
        result_message := FORMAT('Error: %s', SQLERRM);
END;
$$;

CREATE OR REPLACE PROCEDURE audit.sign_off_custom_query(
    IN query_id INT,
    IN signed_off_by_user VARCHAR(255),
    INOUT result_message TEXT DEFAULT NULL
)
LANGUAGE plpgsql SECURITY DEFINER AS $$
DECLARE
    created_by_user VARCHAR(255);
BEGIN
    -- Retrieve the created_by user for the given query ID
    SELECT created_by INTO created_by_user
    FROM audit.custom_queries
    WHERE id = query_id;

    -- Check if the query exists
    IF NOT FOUND THEN
        result_message := FORMAT('Error: Query with id %s does not exist', query_id);
        RETURN;
    END IF;

    -- Check if the created_by user is the same as the signed_off_by user
    IF created_by_user = signed_off_by_user THEN
        result_message := FORMAT('Error: Cannot sign off with the same user who created the query (%s).', signed_off_by_user);
        RETURN;
    END IF;

    -- Update the signed_off_by and signed_off_on fields
    UPDATE audit.custom_queries
    SET signed_off_by = signed_off_by_user,
        signed_off_on = NOW()
    WHERE id = query_id;

    -- Check if the update affected any rows (just for verification)
    IF NOT FOUND THEN
        result_message := FORMAT('Error: Failed to update query with id %s', query_id);
        RETURN;
    END IF;

    result_message := FORMAT('Success: Query with id %s successfully signed off', query_id);

EXCEPTION
    WHEN OTHERS THEN
        -- Capture any unexpected errors
        result_message := FORMAT('Error: %s', SQLERRM);
END;
$$;

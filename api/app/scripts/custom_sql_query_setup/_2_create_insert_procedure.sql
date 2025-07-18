CREATE OR REPLACE PROCEDURE audit.insert_custom_query(
    IN query TEXT,
    IN confirmation_query TEXT,
    IN created_by VARCHAR(255),
    IN expected_before INT,
    IN expected_after INT,
    IN maximum_rows_affected INT,
    INOUT new_id INT
)
LANGUAGE plpgsql SECURITY DEFINER AS $$
BEGIN
    INSERT INTO audit.custom_queries (
        query,
        confirmation_query,
        created_by,
        created_on,
        expected_before,
        expected_after,
        passed,
        maximum_rows_affected
    )
    VALUES (
        query,
        confirmation_query,
        created_by,
        NOW(),
        expected_before,
        expected_after,
        FALSE,
        maximum_rows_affected
    )
    RETURNING id INTO new_id;
END;
$$;

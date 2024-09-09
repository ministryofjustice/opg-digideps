CREATE TABLE custom_queries (
    id SERIAL PRIMARY KEY,
    query TEXT NOT NULL,
    confirmation_query TEXT,
    created_by VARCHAR(255) NOT NULL,
    created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    signed_off_by VARCHAR(255),
    signed_off_on TIMESTAMP,
    run_on TIMESTAMP,
    expected_before INT,
    expected_after INT,
    passed BOOLEAN
);


CREATE OR REPLACE PROCEDURE insert_custom_query(
    IN query TEXT,
    IN confirmation_query TEXT,
    IN created_by VARCHAR(255),
    IN expected_before INT,
    IN expected_after INT,
    INOUT new_id INT
)
LANGUAGE plpgsql AS $$
BEGIN
    INSERT INTO custom_queries (
        query,
        confirmation_query,
        created_by,
        created_on,
        expected_before,
        expected_after,
        passed
    )
    VALUES (
        query,
        confirmation_query,
        created_by,
        NOW(),
        expected_before,
        expected_after,
        FALSE
    )
    RETURNING id INTO new_id;
END;
$$;

CALL insert_custom_query(
    'SELECT * FROM users;',
    'SELECT COUNT(*) FROM users;',
    'admin_user',
    100,
    0,
    NULL
);

select * from custom_queries;

CALL sign_off_custom_query(
    5,
    'scrub_user'
);

CREATE OR REPLACE PROCEDURE sign_off_custom_query(
    IN query_id INT,
    IN signed_off_by_user VARCHAR(255)
)
LANGUAGE plpgsql AS $$
DECLARE
    created_by_user VARCHAR(255);
BEGIN
    -- Retrieve the created_by user for the given query ID
    SELECT created_by INTO created_by_user
    FROM custom_queries
    WHERE id = query_id;

    -- Check if the query exists
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Query with id % does not exist', query_id;
    END IF;

    -- Check if the created_by user is the same as the signed_off_by user
    IF created_by_user = signed_off_by_user THEN
        RAISE EXCEPTION 'Cannot sign off with the same user who created the query (%).', signed_off_by_user;
    END IF;

    -- Update the signed_off_by and signed_off_on fields
    UPDATE custom_queries
    SET signed_off_by = signed_off_by_user,
        signed_off_on = NOW()
    WHERE id = query_id;

    -- Check if the update affected any rows (just for verification)
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Failed to update query with id %', query_id;
    END IF;
END;
$$;




CREATE OR REPLACE PROCEDURE revoke_custom_query(
    IN query_id INT
)
LANGUAGE plpgsql AS $$
DECLARE
    query_run_on TIMESTAMP;
BEGIN
    -- Retrieve the run_on timestamp for the given query ID
    SELECT run_on INTO query_run_on
    FROM custom_queries
    WHERE id = query_id;

    -- Check if the query exists
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Query with id % does not exist', query_id;
    END IF;

    -- Check if the query has already been run
    IF query_run_on IS NOT NULL THEN
        RAISE EXCEPTION 'Cannot revoke query with id % because it has already been run on %', query_id, query_run_on;
    END IF;

    -- Delete the query
    DELETE FROM custom_queries
    WHERE id = query_id;

    -- Check if the delete affected any rows (just for verification)
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Failed to delete query with id %', query_id;
    END IF;
END;
$$;

drop procedure get_custom_query(
query_id INT, out_id INT, out_query text, out_confirmation_query text, out_created_by VARCHAR, out_created_on TIMESTAMP, out_signed_off_by VARCHAR,
out_signed_off_on TIMESTAMP,
    out_run_on TIMESTAMP,
    out_expected_before TIMESTAMP,
    out_expected_after TIMESTAMP,
    out_passed BOOLEAN
);

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
LANGUAGE plpgsql AS $$
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
            RAISE EXCEPTION 'Query with id % does not exist', query_id;
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



call get_custom_query(null,null,null,null,null,null,null,null,null,null,null,false);


call revoke_custom_query(4)


select *
from dd_user du

update dd_user set lastname = 'Bobsical' where id = 7;

select count(*) from dd_user where lastname = 'Manager3' and id = 7;

CALL insert_custom_query(
    'update dd_user set lastname = ''Bobsical'' where id = 7;',
    'select count(*) from dd_user where lastname = ''Manager3'' and id = 7;',
    'phil.jones',
    1,
    0,
    NULL
);


call execute_and_verify_query(5);

CREATE OR REPLACE PROCEDURE execute_and_verify_query(
    IN query_id INT
)
LANGUAGE plpgsql AS $$
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
    FROM custom_queries
    WHERE id = query_id;

    -- Check if the id exists
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Query with id % does not exist', query_id;
    END IF;

    -- Check if signed_off_by is not null
    IF v_signed_off_by IS NULL THEN
        RAISE EXCEPTION 'Query with id % has not been signed off yet', query_id;
    END IF;

    -- Execute the confirmation_query and check the result
    EXECUTE v_confirmation_query INTO v_result_before;

    IF v_result_before != v_expected_before THEN
        RAISE EXCEPTION 'Confirmation query result does not match expected_before for query_id %', query_id;
    END IF;

    -- Set run_on to now
    UPDATE custom_queries
    SET run_on = NOW()
    WHERE id = query_id;

    -- Execute the main query
    EXECUTE v_query;

    -- Re-run the confirmation_query and check the result
    EXECUTE v_confirmation_query INTO v_result_after;

    IF v_result_after != v_expected_after THEN
        RAISE EXCEPTION 'Confirmation query result does not match expected_after for query_id %', query_id;
    END IF;

    -- Update the passed field to True
    UPDATE custom_queries
    SET passed = TRUE
    WHERE id = query_id;

    RAISE NOTICE 'Query with id % executed successfully and marked as passed', query_id;
END;
$$;


select * from dd_user du

- add a lambda that we can call from the command line. The lambda must be able to wrap and check the input
and connect to the DB using the safe_execute_sql role.

{
  "key1": "value1",
  "key2": "value2",
  "key3": "value3"
}

aws lambda invoke --function-name <lambda_function_name> --payload file://input.json output.json


- add a wrapper script to call the lambda:



- write terraform for the lambda
- add role that is assumable by team members that only has execute permission on the lambda
- create the safe_execute_sql db role that only has access to run the 5 stored procs
- add a role that the lambda uses that has permissions to the secret for the safe_execute_sql db role.

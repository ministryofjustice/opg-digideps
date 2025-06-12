CREATE TABLE IF NOT EXISTS audit.custom_queries (
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
    passed BOOLEAN,
    maximum_rows_affected INT
);
ALTER SEQUENCE audit.custom_queries_id_seq CACHE 1;

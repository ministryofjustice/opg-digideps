UPDATE report
SET client_id = r_updates.new_client_id
FROM (
    SELECT
        c1.id AS old_client_id,
        c2.id AS new_client_id,
        r.id AS report_id
    FROM client c1
    INNER JOIN report r
    ON r.client_id = c1.id
    INNER JOIN client c2
    ON c2.case_number = c1.case_number
    WHERE
        c1.deleted_at IS NOT NULL
        AND c2.deleted_at IS NULL
        AND c1.id <> c2.id
) r_updates
WHERE
    client_id = r_updates.old_client_id
    AND id = r_updates.report_id
;

UPDATE deputy_case
SET client_id = dc_updates.new_client_id
FROM (
    SELECT
        c1.id AS old_client_id,
        c2.id AS new_client_id,
        dc.user_id AS dc_user_id
    FROM client c1
    INNER JOIN deputy_case dc
    ON dc.client_id = c1.id
    INNER JOIN client c2
    ON c2.case_number = c1.case_number
    WHERE
        c1.deleted_at IS NOT NULL
        AND c2.deleted_at IS NULL
        AND c1.id <> c2.id
) dc_updates
WHERE
    client_id = dc_updates.old_client_id
    AND user_id = dc_updates.dc_user_id
;

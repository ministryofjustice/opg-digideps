DELETE FROM deputy_case t
WHERE t.client_id IN (
    SELECT DISTINCT c1.id
    FROM client c1
    INNER JOIN deputy_case dc
    ON dc.client_id = c1.id
    INNER JOIN client c2
    ON c2.case_number = c1.case_number
    WHERE
        c1.deleted_at IS NOT NULL
        AND c2.deleted_at IS NULL
        AND c1.id <> c2.id
);

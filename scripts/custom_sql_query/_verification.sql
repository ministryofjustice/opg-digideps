SELECT COUNT(dc.user_id)
FROM client c1
INNER JOIN deputy_case dc
    ON dc.client_id = c1.id
INNER JOIN client c2
    ON c2.case_number = c1.case_number
LEFT JOIN deputy_case v
    ON v.user_id = dc.user_id
    AND v.client_id = c2.id
WHERE
    c1.deleted_at IS NOT NULL
    AND c2.deleted_at IS NULL
    AND c1.id <> c2.id
    AND v.client_id IS NULL
;

SELECT COUNT(r.id)
FROM client c1
INNER JOIN report r
ON r.client_id = c1.id
INNER JOIN client c2
ON c2.case_number = c1.case_number
WHERE
    c1.deleted_at IS NOT NULL
    AND c2.deleted_at IS NULL
    AND c1.id <> c2.id
;

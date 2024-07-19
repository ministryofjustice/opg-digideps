WITH oldest_user_accounts AS
(
SELECT id
FROM (SELECT *, ROW_NUMBER() OVER (PARTITION BY deputy_uid ORDER BY created_at) AS row_number from dd_user) AS t
WHERE t.row_number = 1 AND deputy_uid IS NOT NULL
)

UPDATE dd_user
SET is_primary = true
where id IN (select id from oldest_user_accounts)

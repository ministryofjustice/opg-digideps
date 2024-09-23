
CREATE schema ddls330.mcd;

-- SINGLE DEPUTY ACCOUNTS

-- These covers a single deputy account that is or is not linked to an active client.
CREATE TABLE ddls330.singleAccounts.mcd (
    id int,
    deputy_uid bigint,
    is_primary bool
);

-- Insert all single user accounts that have a deputy uid and is_primary = false
INSERT INTO ddls330.singleAccounts.mcd (id, deputy_uid, is_primary)
    SELECT id, deputy_uid, is_primary
    FROM dd_user WHERE deputy_uid IN (
        SELECT deputy_uid
        FROM dd_user
        WHERE deputy_uid IS NOT NULL
        AND is_primary = FALSE
        GROUP BY deputy_uid
        HAVING COUNT(*) = 1
    ) ORDER BY deputy_uid;


-- Count how many the above returns
SElECT count(*) FROM (
     SELECT deputy_uid
     FROM dd_user
     WHERE deputy_uid IS NOT NULL
     AND is_primary = FALSE
     GROUP BY deputy_uid
     HAVING COUNT(*) = 1
 ) AS count_single_deputy_accounts;


-- Run update command on dd_user table
    UPDATE dd_user
    SET is_primary = true
    WHERE id IN (SELECT id FROM ddls330.singleAccounts.mcd)

-- Run the count query again to assert that the correct number of records have been updated - this should return 0
SElECT count(*) FROM (
     SELECT deputy_uid
     FROM dd_user
     WHERE deputy_uid IS NOT NULL
       AND is_primary = FALSE
     GROUP BY deputy_uid
     HAVING COUNT(*) = 1
 ) AS count_single_deputy_accounts;




-- IDENTIFY ANY DUPLICATE DEPUTY ACCOUNTS WHERE ALL ACCOUNTS ARE NOT LINKED TO AN ACTIVE CLIENT

CREATE table ddls330.allDuplicateAccountsHaveInactiveClients (
  deputy_uid bigint,
  hasActiveClients bool
);

-- Identify all duplicate deputy accounts and group by deputy_uid and 'has active clients' result
INSERT INTO ddls330.allDuplicateAccountsHaveInactiveClients (deputy_uid, hasActiveClients)
SELECT dd.deputy_uid, c.deleted_at IS NULL
FROM dd_user dd
     INNER JOIN deputy_case dc on dd.id = dc.user_id
     INNER JOIN client c on dc.client_id = c.id
    AND dd.deputy_uid IN(
        SELECT deputy_uid
        FROM dd_user
        WHERE deputy_uid IS NOT NULL
        GROUP BY deputy_uid
        HAVING COUNT(*) > 1
    )
GROUP by dd.deputy_uid, c.deleted_at IS NULL
ORDER by dd.deputy_uid;

-- Identify deputy_uids that appear once and do not have any active clients
SELECT * FROM ddls330.allDuplicateAccountsHaveInactiveClients
WHERE hasActiveClients = FALSE
  AND deputy_uid IN (
    SELECT deputy_uid FROM ddls330.allDuplicateAccountsHaveInactiveClients
    GROUP by deputy_uid
    HAVING COUNT(*) = 1
);



-- DUPLICATE DEPUTY ACCOUNTS

-- Create data subset to identify the first created account for duplicate deputies
-- that are attached to one or more active clients
CREATE TABLE ddls330.duplicateDeputyAccountSubset.mcd (
      id int,
      deputy_uid bigint,
      is_primary bool,
);

-- Retrieves all deputy accounts that appear more than once and have an active client
INSERT INTO ddls330.duplicateDeputyAccountSubset.mcd (id, deputy_uid, is_primary)
    SELECT dd.id, dd.deputy_uid, dd.is_primary
    FROM dd_user dd
    INNER JOIN deputy_case dc on dd.id = dc.user_id
    INNER JOIN client c on dc.client_id = c.id
    WHERE c.deleted_at IS NULL
    AND dd.deputy_uid IN (
        SELECT deputy_uid
        FROM dd_user
        WHERE deputy_uid IS NOT NULL
        GROUP BY deputy_uid
        HAVING COUNT(*) > 1
    )
    AND dd.is_primary = false
    ORDER by dd.deputy_uid;


-- Run partition query to identify the first created account from data subset and insert into a table
CREATE TABLE ddls330.duplicateDeputiesFirstAccount.mcd (
      id int,
      deputy_uid bigint,
      is_primary bool
);


INSERT INTO ddls330.duplicateDeputiesFirstAccount.mcd (id, deputy_uid, is_primary)
SELECT id, deputy_uid, is_primary
FROM (SELECT *, ROW_NUMBER() OVER (PARTITION BY deputy_uid ORDER BY id) AS row_number from ddls330.duplicateDeputyAccountSubset.mcd) AS t
WHERE t.row_number = 1 AND deputy_uid IS NOT NULL AND is_primary = FALSE


-- Run update command
UPDATE dd_user
SET is_primary = true
WHERE id IN (SELECT id FROM ddls330.duplicateDeputiesFirstAccount.mcd)

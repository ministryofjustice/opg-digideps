
CREATE schema ddls330;

-- SINGLE DEPUTY ACCOUNTS

-- These covers a single deputy account that is or is not linked to an active client
CREATE TABLE ddls330.singleAccounts (
    id int,
    deputy_uid bigint,
    is_primary bool
);

-- Insert all single user accounts that have a deputy uid and is_primary = false
INSERT INTO ddls330.singleAccounts (id, deputy_uid, is_primary)
    SELECT id, deputy_uid, is_primary
    FROM dd_user WHERE deputy_uid IN (
        SELECT deputy_uid
        FROM dd_user
        WHERE deputy_uid IS NOT NULL
        GROUP BY deputy_uid
        HAVING COUNT(*) = 1
    )
AND is_primary = FALSE
ORDER BY deputy_uid;
-- Inserts 46259 accounts


-- Count how many the above returns
SELECT COUNT(*) FROM dd_user
WHERE deputy_uid IN
    (
         SELECT deputy_uid
         FROM dd_user
         WHERE deputy_uid IS NOT NULL
         GROUP BY deputy_uid
         HAVING COUNT(*) = 1
     )
AND is_primary = FALSE;


-- Run update command on dd_user table
    UPDATE dd_user
    SET is_primary = true
    WHERE id IN (SELECT id FROM ddls330.singleAccounts)





-- IDENTIFY ANY DUPLICATE DEPUTY ACCOUNTS WHERE ALL ACCOUNTS ARE NOT LINKED TO AN ACTIVE CLIENT

CREATE table ddls330.allDuplicateAccounts(
  deputy_uid bigint,
  hasActiveClients bool
);

-- Identify all duplicate deputy accounts and group by deputy_uid and 'has active clients' result
INSERT INTO ddls330.allDuplicateAccounts (deputy_uid, hasActiveClients)
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
-- Inserts 372 accounts


-- SENSE CHECK random records to see what the data looks like
    SELECT dd.id, dd.deputy_uid, c.case_number, c.deleted_at IS NULL
    FROM dd_user dd
         INNER JOIN deputy_case dc on dd.id = dc.user_id
         INNER JOIN client c on dc.client_id = c.id
    WHERE deputy_uid = 'xxx';


-- Identify deputy_uids that appear more than once and do not have any active clients
SELECT * FROM ddls330.allDuplicateAccounts
WHERE hasActiveClients = FALSE
  AND deputy_uid IN (
    SELECT deputy_uid FROM ddls330.allDuplicateAccounts
    GROUP by deputy_uid
    HAVING COUNT(*) = 1
);
-- 1 deputy returned - 700718886638 (ACTION: Check how to proceed with this with Stacey or Nicola)



-- DUPLICATE DEPUTY ACCOUNTS

-- Create data subset to identify duplicate deputies that are attached to one or more active clients
CREATE TABLE ddls330.duplicateDeputyAccountLinkedToActiveClientSubset (
      id int,
      deputy_uid bigint,
      is_primary bool
);

-- Retrieves all deputy accounts that appear more than once and have an active client
INSERT INTO ddls330.duplicateDeputyAccountLinkedToActiveClientSubset (id, deputy_uid, is_primary)
    SELECT dd.id, dd.deputy_uid, dd.is_primary
    FROM dd_user dd
             INNER JOIN deputy_case dc on dd.id = dc.user_id
             INNER JOIN client c on dc.client_id = c.id
    WHERE deputy_uid IN (
        SELECT deputy_uid FROM ddls330.allDuplicateAccounts
        WHERE hasActiveClients = TRUE
    )
    AND c.deleted_at IS NULL
    ORDER BY deputy_uid;
-- Inserts 664 accounts

-- Run partition query to identify the first created account from data subset and inserts into a separate table
CREATE TABLE ddls330.duplicateDeputiesFirstAccount (
      id int,
      deputy_uid bigint,
      is_primary bool
);

INSERT INTO ddls330.duplicateDeputiesFirstAccount (id, deputy_uid, is_primary)
SELECT id, deputy_uid, is_primary
FROM (SELECT *, ROW_NUMBER() OVER (PARTITION BY deputy_uid ORDER BY id) AS row_number from ddls330.duplicateDeputyAccountLinkedToActiveClientSubset) AS t
WHERE t.row_number = 1 AND deputy_uid IS NOT NULL AND is_primary = FALSE;
-- Inserts 334 accounts

-- Run update command
UPDATE dd_user
SET is_primary = true
WHERE id IN (SELECT id FROM ddls330.duplicateDeputiesFirstAccount);


-- Check if any deputy accounts exist that do not have a primary flag
SELECT DISTINCT deputy_uid FROM dd_user
   WHERE deputy_uid NOT IN (
        SELECT DISTINCT deputy_uid
        FROM dd_user
        WHERE is_primary = TRUE);

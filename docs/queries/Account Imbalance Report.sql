-- Manual report functionality in ImbalanceRepository.php

CREATE TEMP VIEW imbalance_report AS (
	WITH report_info AS (
  SELECT
    id,
    balance_mismatch_explanation AS withtext,
    balance_mismatch_explanation AS notext,
    type
  FROM
    report
  WHERE
    submit_date > '2022-12-07'
),
lay AS (
  SELECT
    CAST(
      count(CASE WHEN withtext IS NOT NULL THEN 1 END) AS DECIMAL
    ) AS withtext,
    CAST(
      count(CASE WHEN notext IS NULL THEN 1 END) AS DECIMAL
    ) AS notext
  FROM
    report_info
  WHERE
    type IN ('103', '102', '104', '103-4', '102-4')
),
pa AS (
  SELECT
    CAST(
      count(CASE WHEN withtext IS NOT NULL THEN 1 END) AS DECIMAL
    ) AS withtext,
    CAST(
      count(CASE WHEN notext IS NULL THEN 1 END) AS DECIMAL
    ) AS notext
  FROM
    report_info
  WHERE
    type IN ('103-6', '102-6', '104-6', '103-4-6', '102-4-6')
),
prof AS (
  SELECT
    CAST(
      count(CASE WHEN withtext IS NOT NULL THEN 1 END) AS DECIMAL
    ) AS withtext,
    CAST(
      count(CASE WHEN notext IS NULL THEN 1 END) AS DECIMAL
    ) AS notext
  FROM
    report_info
  WHERE
    type IN ('103-5', '102-5', '104-5', '103-4-5', '102-4-5')
)
SELECT
  'LAY' AS "Deputy Type",
  notext AS "No Imbalance",
  withtext AS "Reported Imbalance",
  ROUND(withtext / (withtext + notext) * 100) AS "Imbalance %",
  withtext + notext AS "Total"
FROM
  lay
UNION
SELECT
  'PA' AS "Deputy Type",
  notext AS "No Imbalance",
  withtext AS "Reported Imbalance",
  ROUND(withtext / (withtext + notext) * 100) AS "Imbalance %",
  withtext + notext AS "Total"
FROM
  pa
UNION
SELECT
  'PROF' AS "Deputy Type",
  notext AS "No Imbalance",
  withtext AS "Reported Imbalance",
  ROUND(withtext / (withtext + notext) * 100) AS "Imbalance %",
  withtext + notext AS "Total"
FROM
  prof
);

\copy (SELECT * FROM imbalance_report) TO 'Imbalance_report.csv' DELIMITER ',' CSV HEADER;
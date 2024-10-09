UPDATE anon.client c0 SET firstname = by_case_number.target_firstname, lastname = by_case_number.target_lastname
FROM
(
	SELECT
	ac1.firstname,
	ac1.lastname,
	ac2.firstname AS target_firstname,
	ac2.lastname AS target_lastname,
	c2.id AS id_target,
	pc2.ppk_id AS ppk_id_target,
	c1.id,
	pc1.ppk_id,
	c2.case_number
	from public.client c1
	INNER JOIN (
		SELECT MIN(id) AS id, case_number
		FROM public.client
		GROUP BY case_number
		HAVING COUNT(*) > 1
	) AS c2 ON c1.case_number = c2.case_number
	INNER JOIN processing.client pc1 on pc1.id = c1.id
	INNER JOIN anon.client ac1 on ac1.ppk_id = pc1.ppk_id
	INNER JOIN processing.client pc2 on pc2.id = c2.id
	INNER JOIN anon.client ac2 on ac2.ppk_id = pc2.ppk_id
) AS by_case_number
WHERE by_case_number.ppk_id = c0.ppk_id;

UPDATE anon.dd_user d0 SET firstname = by_deputy_uid.target_firstname, lastname = by_deputy_uid.target_lastname
FROM
(
	SELECT
	ad1.firstname,
	ad1.lastname,
	ad2.firstname AS target_firstname,
	ad2.lastname AS target_lastname,
	d2.id AS id_target,
	pd2.ppk_id AS ppk_id_target,
	d1.id,
	pd1.ppk_id,
	d2.deputy_uid
	from public.dd_user d1
	INNER JOIN (
		SELECT MIN(id) AS id, deputy_uid
		FROM public.dd_user
		GROUP BY deputy_uid
		HAVING COUNT(*) > 1
	) AS d2 ON d1.deputy_uid = d2.deputy_uid
	INNER JOIN processing.dd_user pd1 on pd1.id = d1.id
	INNER JOIN anon.dd_user ad1 on ad1.ppk_id = pd1.ppk_id
	INNER JOIN processing.dd_user pd2 on pd2.id = d2.id
	INNER JOIN anon.dd_user ad2 on ad2.ppk_id = pd2.ppk_id
) AS by_deputy_uid
WHERE by_deputy_uid.ppk_id = d0.ppk_id;

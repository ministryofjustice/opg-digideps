UPDATE anon.dd_user ad
SET email = LOWER(
	CONCAT(
		COALESCE(firstname, 'none'),
		'.',
		COALESCE(lastname, 'none'),
        '.',
		ppk_id,
		'@',
		SUBSTRING(email FROM POSITION('@' IN email) + 1)
	)
);

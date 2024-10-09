UPDATE anon.dd_user ad
SET email = LOWER(
	CONCAT(
		COALESCE(firstname, 'none'),
		'.',
		COALESCE(lastname, 'none'),
        '.',
        FLOOR(RANDOM()* (99999-0 + 1) + 0)::int,
		'@',
		SUBSTRING(email FROM POSITION('@' IN email) + 1)
	)
);

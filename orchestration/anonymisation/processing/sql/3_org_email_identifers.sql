UPDATE anon.dd_user ad
SET email = LOWER(CONCAT(SUBSTRING(ad.email FROM 1 FOR POSITION('@' IN ad.email)), replace(orguser.email_identifier, '@', '')))
FROM (
	SELECT dd.id, dd.email, o.email_identifier
	FROM public.organisation o
	INNER JOIN public.organisation_user ou ON ou.organisation_id = o.id
	INNER JOIN public.dd_user dd ON dd.id = ou.user_id
) AS orguser,
processing.dd_user pd
WHERE orguser.id = pd.id
AND ad.ppk_id = pd.ppk_id;

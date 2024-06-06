UPDATE public.named_deputy pub1
SET address1 = CASE WHEN NULLIF(proc.address1, '') IS NULL THEN proc.address1 ELSE anon.address1 END,
address2 = CASE WHEN NULLIF(proc.address2, '') IS NULL THEN proc.address2 ELSE anon.address2 END,
address3 = CASE WHEN NULLIF(proc.address3, '') IS NULL THEN proc.address3 ELSE anon.address3 END,
address4 = CASE WHEN NULLIF(proc.address4, '') IS NULL THEN proc.address4 ELSE anon.address4 END,
address5 = CASE WHEN NULLIF(proc.address5, '') IS NULL THEN proc.address5 ELSE anon.address5 END,
address_country = CASE WHEN NULLIF(proc.address_country, '') IS NULL THEN proc.address_country ELSE anon.address_country END,
address_postcode = CASE WHEN NULLIF(proc.address_postcode, '') IS NULL THEN proc.address_postcode ELSE anon.address_postcode END,
deputy_uid = CASE WHEN NULLIF(proc.deputy_uid, '') IS NULL THEN proc.deputy_uid ELSE anon.deputy_uid END,
email1 = CASE WHEN NULLIF(proc.email1, '') IS NULL THEN proc.email1 ELSE anon.email1 END,
email2 = CASE WHEN NULLIF(proc.email2, '') IS NULL THEN proc.email2 ELSE anon.email2 END,
email3 = CASE WHEN NULLIF(proc.email3, '') IS NULL THEN proc.email3 ELSE anon.email3 END,
firstname = CASE WHEN NULLIF(proc.firstname, '') IS NULL THEN proc.firstname ELSE COALESCE(dd_user.firstname, anon.firstname) END,
lastname = CASE WHEN NULLIF(proc.lastname, '') IS NULL THEN proc.lastname ELSE COALESCE(dd_user.lastname, anon.lastname) END,
phone_alternative = CASE WHEN NULLIF(proc.phone_alternative, '') IS NULL THEN proc.phone_alternative ELSE anon.phone_alternative END,
phone_main = CASE WHEN NULLIF(proc.phone_main, '') IS NULL THEN proc.phone_main ELSE anon.phone_main END
FROM public.named_deputy as pub2 INNER JOIN processing.named_deputy AS proc ON pub2.id = proc.id
INNER JOIN (SELECT * FROM anon.named_deputy ORDER BY ppk_id LIMIT 100 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id
LEFT JOIN client ON pub2.id = client.named_deputy_id
LEFT JOIN deputy_case ON client.id = deputy_case.client_id
LEFT JOIN dd_user ON deputy_case.user_id = dd_user.id WHERE pub1.id = pub2.id;

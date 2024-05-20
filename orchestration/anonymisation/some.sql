UPDATE public.pre_registration pub
SET
    client_lastname = CASE WHEN NULLIF(proc.client_lastname, '') IS NULL THEN proc.client_lastname ELSE anon.client_lastname END,
    deputy_address_1 = CASE WHEN NULLIF(proc.deputy_address_1, '') IS NULL THEN proc.deputy_address_1 ELSE anon.deputy_address_1 END,
    deputy_address_2 = CASE WHEN NULLIF(proc.deputy_address_2, '') IS NULL THEN proc.deputy_address_2 ELSE anon.deputy_address_2 END,
    deputy_address_3 = CASE WHEN NULLIF(proc.deputy_address_3, '') IS NULL THEN proc.deputy_address_3 ELSE anon.deputy_address_3 END,
    deputy_address_4 = CASE WHEN NULLIF(proc.deputy_address_4, '') IS NULL THEN proc.deputy_address_4 ELSE anon.deputy_address_4 END,
    deputy_address_5 = CASE WHEN NULLIF(proc.deputy_address_5, '') IS NULL THEN proc.deputy_address_5 ELSE anon.deputy_address_5 END,
    deputy_firstname = CASE WHEN NULLIF(proc.deputy_firstname, '') IS NULL THEN proc.deputy_firstname ELSE anon.deputy_firstname END,
    deputy_lastname = CASE WHEN NULLIF(proc.deputy_lastname, '') IS NULL THEN proc.deputy_lastname ELSE anon.deputy_lastname END,
    deputy_postcode = CASE WHEN NULLIF(proc.deputy_postcode, '') IS NULL THEN proc.deputy_postcode ELSE anon.deputy_postcode END,
    deputy_uid = CASE WHEN NULLIF(proc.deputy_uid, '') IS NULL THEN proc.deputy_uid ELSE anon.deputy_uid END,
    hybrid = CASE WHEN NULLIF(proc.hybrid, '') IS NULL THEN proc.hybrid ELSE anon.hybrid END,
    order_type = CASE WHEN NULLIF(proc.order_type, '') IS NULL THEN proc.order_type ELSE anon.order_type END,
    type_of_report = CASE WHEN NULLIF(proc.type_of_report, '') IS NULL THEN proc.type_of_report ELSE anon.type_of_report END
FROM
    processing.pre_registration AS proc,
    (SELECT * FROM anon.pre_registration ORDER BY ppk_id LIMIT 100 OFFSET 0) AS anon
WHERE pub.id = proc.id
AND proc.ppk_id = anon.ppk_id;





UPDATE public.client pub
SET
    address = CASE WHEN NULLIF(proc.address, '') IS NULL THEN proc.address ELSE anon.address END,
    address2 = CASE WHEN NULLIF(proc.address2, '') IS NULL THEN proc.address2 ELSE anon.address2 END,
    address3 = CASE WHEN NULLIF(proc.address3, '') IS NULL THEN proc.address3 ELSE anon.address3 END,
    address4 = CASE WHEN NULLIF(proc.address4, '') IS NULL THEN proc.address4 ELSE anon.address4 END,
    address5 = CASE WHEN NULLIF(proc.address5, '') IS NULL THEN proc.address5 ELSE anon.address5 END,
    country = CASE WHEN NULLIF(proc.country, '') IS NULL THEN proc.country ELSE anon.country END,
    email = CASE WHEN NULLIF(proc.email, '') IS NULL THEN proc.email ELSE anon.email END,
    firstname = CASE WHEN NULLIF(proc.firstname, '') IS NULL THEN proc.firstname ELSE anon.firstname END,
    lastname = CASE WHEN NULLIF(proc.lastname, '') IS NULL THEN proc.lastname ELSE anon.lastname END,
    phone = CASE WHEN NULLIF(proc.phone, '') IS NULL THEN proc.phone ELSE anon.phone END,
    postcode = CASE WHEN NULLIF(proc.postcode, '') IS NULL THEN proc.postcode ELSE anon.postcode END
FROM
    processing.client AS proc,
    (SELECT * FROM anon.client ORDER BY ppk_id LIMIT 100 OFFSET 100) AS anon
WHERE pub.id = proc.id
AND proc.ppk_id = anon.ppk_id;


UPDATE public.client p1
SET
    address = CASE WHEN NULLIF(proc.address, '') IS NULL THEN proc.address ELSE anon.address END,
    address2 = CASE WHEN NULLIF(proc.address2, '') IS NULL THEN proc.address2 ELSE anon.address2 END,
    address3 = CASE WHEN NULLIF(proc.address3, '') IS NULL THEN proc.address3 ELSE anon.address3 END,
    address4 = CASE WHEN NULLIF(proc.address4, '') IS NULL THEN proc.address4 ELSE anon.address4 END,
    address5 = CASE WHEN NULLIF(proc.address5, '') IS NULL THEN proc.address5 ELSE anon.address5 END,
    country = CASE WHEN NULLIF(proc.country, '') IS NULL THEN proc.country ELSE anon.country END,
    email = CASE WHEN NULLIF(proc.email, '') IS NULL THEN proc.email ELSE anon.email END,
    firstname = CASE WHEN NULLIF(proc.firstname, '') IS NULL THEN proc.firstname ELSE coalesce(pr3.deputy_firstname, anon.firstname) END,
    lastname = CASE WHEN NULLIF(proc.lastname, '') IS NULL THEN proc.lastname ELSE anon.lastname END,
    phone = CASE WHEN NULLIF(proc.phone, '') IS NULL THEN proc.phone ELSE anon.phone END,
    postcode = CASE WHEN NULLIF(proc.postcode, '') IS NULL THEN proc.postcode ELSE anon.postcode END
FROM
    public.client as pub
    INNER JOIN processing.client proc ON pub.id = proc.id
    INNER JOIN (SELECT * FROM anon.client ORDER BY ppk_id LIMIT 100 OFFSET 100) AS anon ON proc.ppk_id = anon.ppk_id
    LEFT JOIN public.pre_registration pr ON pr.client_case_number = pub.case_number
    LEFT JOIN processing.pre_registration pr2 ON pr.id = pr2.id
    LEFT JOIN anon.pre_registration pr3 ON pr2.ppk_id = pr3.ppk_id
where p1.id = pub.id;






UPDATE public.client
SET
    address = CASE WHEN NULLIF(proc.address, '') IS NULL THEN proc.address ELSE anon.address END,
    address2 = CASE WHEN NULLIF(proc.address2, '') IS NULL THEN proc.address2 ELSE anon.address2 END,
    address3 = CASE WHEN NULLIF(proc.address3, '') IS NULL THEN proc.address3 ELSE anon.address3 END,
    address4 = CASE WHEN NULLIF(proc.address4, '') IS NULL THEN proc.address4 ELSE anon.address4 END,
    address5 = CASE WHEN NULLIF(proc.address5, '') IS NULL THEN proc.address5 ELSE anon.address5 END,
    country = CASE WHEN NULLIF(proc.country, '') IS NULL THEN proc.country ELSE anon.country END,
    email = CASE WHEN NULLIF(proc.email, '') IS NULL THEN proc.email ELSE anon.email END,
    firstname = CASE WHEN NULLIF(proc.firstname, '') IS NULL THEN proc.firstname ELSE coalesce(pr3.deputy_firstname, anon.firstname) END,
    lastname = CASE WHEN NULLIF(proc.lastname, '') IS NULL THEN proc.lastname ELSE anon.lastname END,
    phone = CASE WHEN NULLIF(proc.phone, '') IS NULL THEN proc.phone ELSE anon.phone END,
    postcode = CASE WHEN NULLIF(proc.postcode, '') IS NULL THEN proc.postcode ELSE anon.postcode END
FROM
    public.client as pub
    INNER JOIN processing.client proc ON pub.id = proc.id
    INNER JOIN (SELECT * FROM anon.client ORDER BY ppk_id LIMIT 100 OFFSET 100) AS anon ON proc.ppk_id = anon.ppk_id;
    LEFT JOIN public.pre_registration pr ON pr.client_case_number = pub.case_number
    LEFT JOIN processing.pre_registration pr2 ON pr.id = pr2.id
    LEFT JOIN anon.pre_registration pr3 ON pr2.ppk_id = pr3.ppk_id;




SELECT
    CASE WHEN NULLIF(proc.firstname, '') IS NULL THEN proc.firstname ELSE anon.firstname END
FROM
    processing.client AS proc,
    (SELECT * FROM anon.client ORDER BY ppk_id LIMIT 100 OFFSET 100) AS anon
WHERE pub.id = proc.id
AND proc.ppk_id = anon.ppk_id;






UPDATE public.pre_registration pub1
SET client_lastname = CASE WHEN NULLIF(proc.client_lastname, '') IS NULL THEN proc.client_lastname ELSE anon.client_lastname END,
deputy_address_1 = CASE WHEN NULLIF(proc.deputy_address_1, '') IS NULL THEN proc.deputy_address_1 ELSE anon.deputy_address_1 END,
deputy_address_2 = CASE WHEN NULLIF(proc.deputy_address_2, '') IS NULL THEN proc.deputy_address_2 ELSE anon.deputy_address_2 END,
deputy_address_3 = CASE WHEN NULLIF(proc.deputy_address_3, '') IS NULL THEN proc.deputy_address_3 ELSE anon.deputy_address_3 END,
deputy_address_4 = CASE WHEN NULLIF(proc.deputy_address_4, '') IS NULL THEN proc.deputy_address_4 ELSE anon.deputy_address_4 END,
deputy_address_5 = CASE WHEN NULLIF(proc.deputy_address_5, '') IS NULL THEN proc.deputy_address_5 ELSE anon.deputy_address_5 END,
deputy_firstname = CASE WHEN NULLIF(proc.deputy_firstname, '') IS NULL THEN proc.deputy_firstname ELSE COALESCE(dd_user.firstname, anon.deputy_firstname) END,
deputy_lastname = CASE WHEN NULLIF(proc.deputy_lastname, '') IS NULL THEN proc.deputy_lastname ELSE anon.deputy_lastname END,
deputy_postcode = CASE WHEN NULLIF(proc.deputy_postcode, '') IS NULL THEN proc.deputy_postcode ELSE anon.deputy_postcode END,
deputy_uid = CASE WHEN NULLIF(proc.deputy_uid, '') IS NULL THEN proc.deputy_uid ELSE anon.deputy_uid END,
hybrid = CASE WHEN NULLIF(proc.hybrid, '') IS NULL THEN proc.hybrid ELSE anon.hybrid END,
order_type = CASE WHEN NULLIF(proc.order_type, '') IS NULL THEN proc.order_type ELSE anon.order_type END,
type_of_report = CASE WHEN NULLIF(proc.type_of_report, '') IS NULL THEN proc.type_of_report ELSE anon.type_of_report END
FROM public.pre_registration as pub2 INNER JOIN processing.pre_registration AS proc ON pub2.id = proc.id
INNER JOIN (SELECT * FROM anon.pre_registration ORDER BY ppk_id LIMIT 100 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id
LEFT JOIN client ON pre_registration.client_case_number = client.case_number
LEFT JOIN client ON client.case_number = client.client_id
LEFT JOIN deputy_case ON client.client_id = deputy_case.client_id
LEFT JOIN deputy_case ON deputy_case.client_id = deputy_case.user_id
LEFT JOIN dd_user ON deputy_case.user_id = dd_user.id
WHERE pub1.id = pub2.id;



UPDATE public.pre_registration pre_registration SET client_lastname = CASE WHEN NULLIF(proc.client_lastname, '') IS NULL THEN proc.client_lastname ELSE anon.client_lastname END,
deputy_address_1 = CASE WHEN NULLIF(proc.deputy_address_1, '') IS NULL THEN proc.deputy_address_1 ELSE anon.deputy_address_1 END,
deputy_address_2 = CASE WHEN NULLIF(proc.deputy_address_2, '') IS NULL THEN proc.deputy_address_2 ELSE anon.deputy_address_2 END,
deputy_address_3 = CASE WHEN NULLIF(proc.deputy_address_3, '') IS NULL THEN proc.deputy_address_3 ELSE anon.deputy_address_3 END,
deputy_address_4 = CASE WHEN NULLIF(proc.deputy_address_4, '') IS NULL THEN proc.deputy_address_4 ELSE anon.deputy_address_4 END,
deputy_address_5 = CASE WHEN NULLIF(proc.deputy_address_5, '') IS NULL THEN proc.deputy_address_5 ELSE anon.deputy_address_5 END,
deputy_firstname = CASE WHEN NULLIF(proc.deputy_firstname, '') IS NULL THEN proc.deputy_firstname ELSE COALESCE(dd_user.firstname, anon.deputy_firstname) END,
deputy_lastname = CASE WHEN NULLIF(proc.deputy_lastname, '') IS NULL THEN proc.deputy_lastname ELSE anon.deputy_lastname END,
deputy_postcode = CASE WHEN NULLIF(proc.deputy_postcode, '') IS NULL THEN proc.deputy_postcode ELSE anon.deputy_postcode END,
deputy_uid = CASE WHEN NULLIF(proc.deputy_uid, '') IS NULL THEN proc.deputy_uid ELSE anon.deputy_uid END,
hybrid = CASE WHEN NULLIF(proc.hybrid, '') IS NULL THEN proc.hybrid ELSE anon.hybrid END,
order_type = CASE WHEN NULLIF(proc.order_type, '') IS NULL THEN proc.order_type ELSE anon.order_type END,
type_of_report = CASE WHEN NULLIF(proc.type_of_report, '') IS NULL THEN proc.type_of_report ELSE anon.type_of_report END
FROM public.pre_registration as pub2 INNER JOIN processing.pre_registration AS proc ON pub2.id = proc.id
INNER JOIN (SELECT * FROM anon.pre_registration ORDER BY ppk_id LIMIT 100 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id
LEFT JOIN client ON pre_registration.client_case_number = client.case_number
LEFT JOIN deputy_case ON client.client_id = deputy_case.client_id
WHERE pre_registration.id = pub2.id;




UPDATE public.pre_registration pub1
SET client_lastname = CASE WHEN NULLIF(proc.client_lastname, '') IS NULL THEN proc.client_lastname ELSE anon.client_lastname END,
deputy_address_1 = CASE WHEN NULLIF(proc.deputy_address_1, '') IS NULL THEN proc.deputy_address_1 ELSE anon.deputy_address_1 END,
deputy_address_2 = CASE WHEN NULLIF(proc.deputy_address_2, '') IS NULL THEN proc.deputy_address_2 ELSE anon.deputy_address_2 END,
deputy_address_3 = CASE WHEN NULLIF(proc.deputy_address_3, '') IS NULL THEN proc.deputy_address_3 ELSE anon.deputy_address_3 END,
deputy_address_4 = CASE WHEN NULLIF(proc.deputy_address_4, '') IS NULL THEN proc.deputy_address_4 ELSE anon.deputy_address_4 END,
deputy_address_5 = CASE WHEN NULLIF(proc.deputy_address_5, '') IS NULL THEN proc.deputy_address_5 ELSE anon.deputy_address_5 END,
deputy_firstname = CASE WHEN NULLIF(proc.deputy_firstname, '') IS NULL THEN proc.deputy_firstname ELSE COALESCE(dd_user.firstname, anon.deputy_firstname) END,
deputy_lastname = CASE WHEN NULLIF(proc.deputy_lastname, '') IS NULL THEN proc.deputy_lastname ELSE anon.deputy_lastname END,
deputy_postcode = CASE WHEN NULLIF(proc.deputy_postcode, '') IS NULL THEN proc.deputy_postcode ELSE anon.deputy_postcode END,
deputy_uid = CASE WHEN NULLIF(proc.deputy_uid, '') IS NULL THEN proc.deputy_uid ELSE anon.deputy_uid END,
hybrid = CASE WHEN NULLIF(proc.hybrid, '') IS NULL THEN proc.hybrid ELSE anon.hybrid END,
order_type = CASE WHEN NULLIF(proc.order_type, '') IS NULL THEN proc.order_type ELSE anon.order_type END,
type_of_report = CASE WHEN NULLIF(proc.type_of_report, '') IS NULL THEN proc.type_of_report ELSE anon.type_of_report END
FROM public.pre_registration as pub2 INNER JOIN processing.pre_registration AS proc ON pub2.id = proc.id
INNER JOIN (SELECT * FROM anon.pre_registration ORDER BY ppk_id LIMIT 100 OFFSET 0) AS anon ON proc.ppk_id = anon.ppk_id
LEFT JOIN client ON pub2.client_case_number = client.case_number
LEFT JOIN deputy_case ON client.id = deputy_case.client_id
LEFT JOIN dd_user ON deputy_case.user_id = dd_user.id
WHERE pub1.id = pub2.id;


pre_registration.client_case_number#client.case_number#client.client_id#deputy_case.client_id#deputy_case.user_id#dd_user.id~firstname



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

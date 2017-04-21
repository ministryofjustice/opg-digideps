--Sets up the data required to run the pa team tests.
--Requires a clean database
INSERT INTO dd_team (team_name) VALUES (NULL);
INSERT INTO dd_user (firstname, lastname, password, email, active, registration_date, registration_token, token_date, address_postcode, address_country, phone_main, last_logged_in, deputy_no, odr_enabled, ad_managed, role_name, job_title, agree_terms_use, agree_terms_use_date)
    VALUES ('John Named', 'Green', '9k4PZrYAhWIMcVCELlGk/xJmzYtFLGmta924lBP/VvM4T7sfEDomfn373dueeyh+CADl/aPlzOQV0h+3h1N3Wg==', 'behat-pa1@publicguardian.gsi.gov.uk', TRUE, CURRENT_TIMESTAMP, '', CURRENT_TIMESTAMP, 'SW1', 'GB', '10000000001', CURRENT_TIMESTAMP, 9000001, NULL, NULL, 'ROLE_PA', 'Solicitor', true, CURRENT_TIMESTAMP);
INSERT INTO user_team VALUES (5, 1);
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000010', 'CLY1', 'HENT1');
INSERT INTO deputy_case VALUES (1, 5);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (1, 102, '2017-03-19', NULL, NULL, TRUE);

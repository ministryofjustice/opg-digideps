--Sets up the data required to run the pa team tests.
--Requires a clean database
INSERT INTO dd_team (team_name) VALUES (NULL);
INSERT INTO dd_user (firstname, lastname, password, email, active, registration_date, registration_token, token_date, address_postcode, address_country, phone_main, last_logged_in, deputy_no, odr_enabled, ad_managed, role_name, job_title, agree_terms_use, agree_terms_use_date)
    VALUES ('John Named', 'Green', '9k4PZrYAhWIMcVCELlGk/xJmzYtFLGmta924lBP/VvM4T7sfEDomfn373dueeyh+CADl/aPlzOQV0h+3h1N3Wg==', 'behat-pa1@publicguardian.gsi.gov.uk', TRUE, CURRENT_TIMESTAMP, '', CURRENT_TIMESTAMP, 'SW1', 'GB', '10000000001', CURRENT_TIMESTAMP, 9000001, NULL, NULL, 'ROLE_PA', 'Solicitor', true, CURRENT_TIMESTAMP);
INSERT INTO user_team VALUES (5, 1);

INSERT INTO client (case_number, firstname, lastname) VALUES ('1000010', 'CLY1', 'HENT1');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1138393T', 'CLY2', 'HENT2');
INSERT INTO client (case_number, firstname, lastname) VALUES ('11498120', 'CLY3', 'HENT3');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000011', 'CLY4', 'HENT4');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000012', 'CLY5', 'HENT5');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000013', 'CLY6', 'HENT6');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000014', 'CLY7', 'HENT');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000015', 'CLY8', 'HENT8');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000016', 'CLY9', 'HENT9');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000017', 'CLY10', 'HENT10');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000018', 'CL11', 'HENT11');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000019', 'CLY12', 'HENT12');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000020', 'CLY13', 'HENT13');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000021', 'CLY14', 'HENT14');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000022', 'CLY15', 'HENT15');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000023', 'CLY16', 'HENT16');
INSERT INTO client (case_number, firstname, lastname) VALUES ('1000024', 'CLY17', 'HENT17');

INSERT INTO deputy_case VALUES (1, 5);
INSERT INTO deputy_case VALUES (2, 5);
INSERT INTO deputy_case VALUES (3, 5);
INSERT INTO deputy_case VALUES (4, 5);
INSERT INTO deputy_case VALUES (5, 5);
INSERT INTO deputy_case VALUES (6, 5);
INSERT INTO deputy_case VALUES (7, 5);
INSERT INTO deputy_case VALUES (8, 5);
INSERT INTO deputy_case VALUES (9, 5);
INSERT INTO deputy_case VALUES (10, 5);
INSERT INTO deputy_case VALUES (11, 5);
INSERT INTO deputy_case VALUES (12, 5);
INSERT INTO deputy_case VALUES (13, 5);
INSERT INTO deputy_case VALUES (14, 5);
INSERT INTO deputy_case VALUES (15, 5);
INSERT INTO deputy_case VALUES (16, 5);
INSERT INTO deputy_case VALUES (17, 5);

INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (1, 102, '2017-03-19', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (2, 102, '2017-10-01', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (3, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (4, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (5, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (6, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (7, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (8, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (9, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (10, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (11, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (12, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (13, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (14, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (15, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (16, 102, '2017-05-28', NULL, NULL, TRUE);
INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (17, 102, '2017-05-28', NULL, NULL, TRUE);

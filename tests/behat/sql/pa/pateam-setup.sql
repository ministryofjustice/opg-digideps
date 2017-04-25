--Sets up the data required to run the pa team tests.
DO $$
DECLARE
  teamId integer;
  userId integer;
  clientId integer;
BEGIN
  --Insert behat-pa1@publicguardian.gsi.gov.uk user and add to team
  INSERT INTO dd_team (team_name) VALUES (NULL) RETURNING id INTO teamId;
  INSERT INTO dd_user (firstname, lastname, password, email, active, registration_date, registration_token, token_date, address_postcode, address_country, phone_main, last_logged_in, deputy_no, odr_enabled, ad_managed, role_name, job_title, agree_terms_use, agree_terms_use_date)
  VALUES ('John Named', 'Green', '9k4PZrYAhWIMcVCELlGk/xJmzYtFLGmta924lBP/VvM4T7sfEDomfn373dueeyh+CADl/aPlzOQV0h+3h1N3Wg==', 'behat-pa1@publicguardian.gsi.gov.uk', TRUE, CURRENT_TIMESTAMP, '', CURRENT_TIMESTAMP, 'SW1', 'GB', '10000000001', CURRENT_TIMESTAMP, 9000001, NULL, NULL, 'ROLE_PA', 'Solicitor', true, CURRENT_TIMESTAMP)
  RETURNING id INTO userId;
  INSERT INTO user_team VALUES (userId, teamId);

  --Client data from behat-pa.csv
  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000010', 'CLY1', 'HENT1') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-03-19', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1138393T', 'CLY2', 'HENT2') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-10-01', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('11498120', 'CLY3', 'HENT3') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000011', 'CLY4', 'HENT4') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000012', 'CLY5', 'HENT5') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000013', 'CLY6', 'HENT6') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000014', 'CLY7', 'HENT') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000015', 'CLY8', 'HENT8') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000016', 'CLY9', 'HENT9') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000017', 'CLY10', 'HENT10') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000018', 'CL11', 'HENT11') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000019', 'CLY12', 'HENT12') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000020', 'CLY13', 'HENT13') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000021', 'CLY14', 'HENT14') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000022', 'CLY15', 'HENT15') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000023', 'CLY16', 'HENT16') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);

  INSERT INTO client (case_number, firstname, lastname) VALUES ('1000024', 'CLY17', 'HENT17') RETURNING id INTO clientId;
  INSERT INTO deputy_case VALUES (clientId, userId);
  INSERT INTO report (client_id, type, end_date, no_asset_to_add, no_transfers_to_add, report_seen) VALUES (clientId, 102, '2017-05-28', NULL, NULL, TRUE);
END $$;
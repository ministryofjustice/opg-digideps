--Clear all data and users required by pa team behat tests
DO $$
DECLARE
  maxId integer;
BEGIN
  SET client_min_messages TO WARNING;
  TRUNCATE TABLE report,deputy_case,client,user_team,dd_team RESTART IDENTITY CASCADE;
  DELETE FROM dd_user WHERE email NOT IN (
    'ad@publicguardian.gsi.gov.uk',
    'laydeputy@publicguardian.gsi.gov.uk',
    'laydeputyodr@publicguardian.gsi.gov.uk',
    'admin@publicguardian.gsi.gov.uk');
  SELECT MAX(id) FROM dd_user INTO maxId;
  PERFORM SETVAL('dd_user_id_seq', maxId);
END $$;
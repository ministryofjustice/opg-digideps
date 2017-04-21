--Clear all data and users required by pa team tests
truncate table report,deputy_case,client,user_team,dd_team RESTART IDENTITY cascade;
DELETE FROM dd_user WHERE id > 4;
ALTER SEQUENCE dd_user_id_seq RESTART WITH 5;

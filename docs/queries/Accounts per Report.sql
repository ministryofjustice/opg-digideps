/*
 These functions allows us to view the number of accounts associated with each report that has been submitted.
 The number_of_accounts_last_6_months() function returns the report type and the number of accounts.
 The report type can be used to group the data by deputy type is required.
 In its current implementation, the query only returns the last 6 months worth of data,
 but this can be modified by changing the interval period.
 */


create or replace function get_number_of_accounts(reportId int)
returns int
language plpgsql
as
$$
declare
number_of_accounts int;
begin
SELECT COUNT(*)
INTO number_of_accounts
FROM account
WHERE account.report_id = reportId;

return number_of_accounts;
end;
$$;

create or replace function number_of_accounts_last_6_months()
returns table(report_type varchar, number_of_accounts int)
language plpgsql
as
$$
DECLARE
temprow RECORD;
BEGIN FOR temprow IN
SELECT * FROM report WHERE submit_date > CURRENT_DATE - INTERVAL '6 months'
    LOOP
    RETURN QUERY
SELECT temprow.type, get_number_of_accounts(temprow.id);
END LOOP;
END;
$$;

select number_of_accounts_last_6_months()

DROP FUNCTION get_number_of_accounts(int);
DROP FUNCTION number_of_accounts_last_6_months();

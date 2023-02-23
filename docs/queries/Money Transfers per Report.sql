/*
 These functions allows us to view the number of Money Transfers associated with each report that has been submitted.
 The number_of_transfers_last_6_months() function returns the report type and the number of money transfers.
 The report type can be used to group the data by deputy type is required.
 In its current implementation, the query only returns the last 6 months worth of data,
 but this can be modified by changing the interval period.
 Also, only 102 variations of the report (high assets) contain a money transfer section.
 */


create or replace function get_number_of_transfers(reportId int)
returns int
language plpgsql
as
$$
declare
number_of_transfers int;
begin
SELECT COUNT(*)
INTO number_of_transfers
FROM money_transfer
WHERE money_transfer.report_id = reportId;

return number_of_transfers;
end;
$$;

create or replace function number_of_transfers_last_6_months()
returns table(report_type varchar, number_of_transfers int)
language plpgsql
as
$$
DECLARE
temprow RECORD;
BEGIN FOR temprow IN
SELECT * FROM report WHERE report.type IN ('102', '102-4', '102-5', '102-4-5', '102-6', '102-4-6') AND submit_date > CURRENT_DATE - INTERVAL '6 months'
    LOOP
    RETURN QUERY
SELECT temprow.type, get_number_of_transfers(temprow.id);
END LOOP;
END;
$$;

select number_of_transfers_last_6_months()

DROP FUNCTION get_number_of_transfers(int);
DROP FUNCTION number_of_transfers_last_6_months();

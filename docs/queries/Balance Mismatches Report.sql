/*
 These functions allows us to determine how many reports were submitted with a balance mismatch, and how big the mismatch was.
 The balance_mismatches_last_6_months() function returns the report type and the balance mismatch in £.
 The report type can be used to group the data by deputy type is required.
 In its current implementation, the query only returns the last 6 months worth of data,
 but this can be modified by changing the interval period.

 One thing to note is that both money in and money out are stored in the money_transaction table.
 There is currently no clear way of telling which entries are money in or money out.
 The get_total_money_in() and get_total_money_out() function use the hard-coded categories from
 the MoneyTransaction entity to translate the entries into money in or money out.
 Please make sure to check the categories listed in the query below match the categories in the MoneyTransaction entity.
 */


create or replace function get_total_money_out(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_money_out numeric(14,2);
begin
SELECT SUM(amount)
INTO total_money_out
FROM money_transaction
WHERE report_id = reportId
  AND category IN (
                   'care-fees',
                   'local-authority-charges-for-care',
                   'medical-expenses',
                   'medical-insurance',
                   'broadband',
                   'council-tax',
                   'dual-fuel',
                   'electricity',
                   'food',
                   'gas',
                   'insurance-eg-life-home-contents',
                   'property-maintenance-improvement',
                   'telephone',
                   'tv-services',
                   'water',
                   'accommodation-service-charge',
                   'mortgage',
                   'rent',
                   'client-transport-bus-train-taxi-fares',
                   'clothes',
                   'day-trips',
                   'holidays',
                   'personal-allowance-pocket-money',
                   'toiletries',
                   'deputy-security-bond',
                   'opg-fees',
                   'professional-fees-eg-solicitor-accountant',
                   'professional-fees-eg-solicitor-accountant-non-lay',
                   'deputy-fees-and-expenses',
                   'investment-bonds-purchased',
                   'investment-account-purchased',
                   'stocks-and-shares-purchased',
                   'purchase-over-1000',
                   'bank-charges',
                   'credit-cards-charges',
                   'loans',
                   'tax-payments-to-hmrc',
                   'unpaid-care-fees',
                   'cash-withdrawn',
                   'transfers-out-to-other-accounts',
                   'anything-else-paid-out');

return total_money_out;
end;
$$;

create or replace function get_total_money_in(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_money_in numeric(14,2);
begin
SELECT SUM(amount)
INTO total_money_in
FROM money_transaction
WHERE report_id = reportId
  AND category IN (
       'salary-or-wages',
       'account-interest',
       'dividends',
       'income-from-property-rental',
       'personal-pension',
       'state-pension',
       'attendance-allowance',
       'disability-living-allowance',
       'employment-support-allowance',
       'housing-benefit',
       'incapacity-benefit',
       'income-support',
       'pension-credit',
       'personal-independence-payment',
       'severe-disablement-allowance',
       'universal-credit',
       'winter-fuel-cold-weather-payment',
       'other-benefits',
       'compensation-or-damages-award',
       'bequest-or-inheritance',
       'cash-gift-received',
       'refunds',
       'sale-of-asset',
       'sale-of-investment',
       'sale-of-property',
       'anything-else');

return total_money_in;
end;
$$;

create or replace function get_open_balance(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_opening_balance numeric(14,2);
begin
select SUM(opening_balance)
into total_opening_balance
from account
where account.report_id = reportId;

return total_opening_balance;
end;
$$;

create or replace function get_closing_balance(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_closing_balance numeric(14,2);
begin
select SUM(closing_balance)
into total_closing_balance
from account
where account.report_id = reportId;

return total_closing_balance;
end;
$$;

create or replace function get_gifts_amount(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_gifts_amount numeric(14,2);
begin
select SUM(amount)
into total_gifts_amount
from gift
where gift.report_id = reportId;

return COALESCE(total_gifts_amount, 0);
end;
$$;

create or replace function get_expense_amount(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_expense_amount numeric(14,2);
begin
select SUM(amount)
into total_expense_amount
from expense
where expense.report_id = reportId;

return COALESCE(total_expense_amount, 0);
end;
$$;

create or replace function calculate_balance_mismatch(reportId int)
returns numeric(14,2)
language plpgsql
as
$$
declare
total_opening_balance numeric(14,2);
    total_closing_balance numeric(14,2);
    total_money_in numeric(14,2);
    total_money_out numeric(14,2);
    total_expense_amount numeric(14,2);
    total_gift_amount numeric(14,2);
begin
select get_open_balance(reportId) into total_opening_balance;
select get_closing_balance(reportId) into total_closing_balance;
select get_total_money_in(reportId) into total_money_in;
select get_total_money_out(reportId) into total_money_out;
select get_expense_amount(reportId) into total_expense_amount;
select get_gifts_amount(reportId) into total_gift_amount;

return abs((((total_opening_balance + total_money_in) - (total_money_out + total_expense_amount + total_gift_amount) - total_closing_balance)));
end;
$$;

create or replace function balance_mismatches_last_6_months()
returns table(report_type varchar, balance_mismatch numeric(14,2))
language plpgsql
as
$$
DECLARE
temprow RECORD;
BEGIN FOR temprow IN
SELECT * FROM report WHERE balance_mismatch_explanation is not null AND submit_date > CURRENT_DATE - INTERVAL '6 months'
    LOOP
    RETURN QUERY
SELECT temprow.type, calculate_balance_mismatch(temprow.id);
END LOOP;
END;
$$;

select balance_mismatches_last_6_months()


DROP FUNCTION get_total_money_out(int);
DROP FUNCTION get_total_money_in(int);
DROP FUNCTION get_open_balance(int);
DROP FUNCTION get_closing_balance(int);
DROP FUNCTION get_gifts_amount(int);
DROP FUNCTION get_expense_amount(int);
DROP FUNCTION calculate_balance_mismatch(int);
DROP FUNCTION balance_mismatches_last_6_months();

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use \Symfony\Component\Yaml\Parser;

/**
 * Auto-generated Migration', false], Please modify to your needs!
 */
class Version018 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        $rows = [
            [1, 'in', 'disability_living_allowance_or_personal_independence_payment', false],
            [2, 'in', 'attendance_allowance', false],
            [3, 'in', 'employment_support_allowance_or_incapacity_benefit', false],
            [4, 'in', 'severe_disablement_allowance', false],
            [5, 'in', 'income_support_or_pension_credit', false],
            [6, 'in', 'housing_benefit', false],
            [7, 'in', 'state_pension', false],
            [8, 'in', 'universal_credit', false],
            [9, 'in', 'other_benefits_eg_winter_fuel_or_cold_weather_payments', false],
            [10, 'in', 'occupational_pensions', false],
            [11, 'in', 'account_interest', false],
            [12,'in', 'income_from_investments_property_or_dividends', false],
            [13, 'in', 'salary_or_wages', false],
            [14, 'in', 'refunds', false],
            [15, 'in', 'bequests_eg_inheritance_gifts_received', false],
            [16, 'in', 'sale_of_investments_property_or_assets', true],
            [17, 'in', 'compensation_or_damages_awards', true],
            [18, 'in', 'transfers_in_from_client_s_other_accounts', true],
            [19, 'in', 'any_other_money_paid_in_and_not_listed_above', true],
            #
            [1, 'out', 'care_fees_or_local_authority_charges_for_care', false],
            [2, 'out', 'accommodation_costs_eg_rent_mortgage_service_charges', false],
            [3, 'out', 'household_bills_eg_water_gas_electricity_phone_council_tax', false],
            [4, 'out', 'day_to_day_living_costs_eg_food_toiletries_clothing_sundries', false],
            [5, 'out', 'debt_payments_eg_loans_cards_care_fee_arrears', false],
            [6, 'out', 'travel_costs_for_client_eg_bus_train_taxi_fares', false],
            [7, 'out', 'holidays_or_day_trips', false],
            [8, 'out', 'tax_payable_to_hmrc', false],
            [9 ,'out', 'insurance_eg_life_home_and_contents', false],
            [10, 'out', 'office_of_the_public_guardian_fees', false],
            [11, 'out', 'deputy_s_security_bond', false],
            [12, 'out', 'client_s_personal_allowance_eg_spending_money', true],
            [13, 'out', 'cash_withdrawals', true],
            [14, 'out', 'professional_fees_eg_solicitor_or_accountant_fees', true],
            [15, 'out', 'deputy_s_expenses', true],
            [16, 'out', 'gifts', true],
            [17, 'out', 'major_purchases_eg_property_vehicles', true],
            [18, 'out', 'property_maintenance_or_improvement', true],
            [19, 'out', 'investments_eg_shares_bonds_savings', true],
            [20, 'out', 'transfers_out_to_other_client_s_accounts', true],
            [21, 'out', 'any_other_money_paid_out_and_not_listed_above', false]
        ];
        foreach ($rows as $row) {
            list($displayOrder, $type, $id, $hasMoreDetails) = $row;
            $hasMoreDetailsBool = $hasMoreDetails ? 'true' :'false';
            $sql = "INSERT INTO account_transaction_type (display_order, type, id, has_more_details) 
                  VALUES('$displayOrder', '$type', '$id', '$hasMoreDetailsBool')";
             $this->addSql($sql);
            
        }
       
    }

    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM account_transaction_type");
    }

}
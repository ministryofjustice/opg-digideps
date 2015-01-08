<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version2 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql("INSERT INTO role (name) VALUES ('OPG Administrator'), ('Lay Deputy'),('Professional Deputy'),('Local Authority Deputy'),('Visitor')");
        $this->addSql("INSERT INTO title (title) VALUES ('Mr'),('Mrs'),('Miss'),('Ms'),('Dr'),('Professor'),('Sir'),('Dame'),('Lord'),('Baroness')");
        $this->addSql("INSERT INTO dd_user (title_id,firstname, role_id, password, email, active, password_salt, registration_date, registration_token, email_confirmed, lastname) VALUES (1,'test',1, md5('aFGQ475SDsdfsaf2342' || 'test' || 'bGGQ485SDsdfsaf6790'),'deputyshipservice@publicguardian.gsi.gov.uk',TRUE,'bGGQ485SDsdfsaf6790','2014-06-09','testtoken',TRUE,'Test')");

        $this->addSql("INSERT INTO court_order_type (name) VALUES ('Health & Welfare'), ('Property and Affairs')");
        
        //insert benefit_type
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (1, 'Disability Living Allowance', 'disability_living_allowance', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (2, 'Attendance Allowance', 'attendance_allowance', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (3, 'Employment Support Allowance', 'employment_support_allowance', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (4, 'Incapacity Benefit', 'incapacity_benefit', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (5, 'Severe Disablement Allowance', 'severe_disablement_allowance', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (6, 'Income Support/Pension Credit', 'income_support_pension_credit', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (7, 'Housing Benefit', 'housing_benefit', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (8, 'State Pension', 'state_pension', false)");
        $this->addSql("INSERT INTO benefit_type (id, name, form_name, payment_description_required) VALUES (9, 'Other Benefit*', 'other_benefit', true)");
        
        //load income type
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (1, 'Occupational pension(s)', 'occupational_pension', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (2, 'Account interest', 'account_interest', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (3, 'Income from investments/property', 'income_from_investments_property', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (4, 'Sale of investments/property/assets*', 'sale_of_investments_property_assets', true)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (5, 'Transfers from other accounts/Court funds', 'transfers_from other_accounts_court_funds', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (6, 'Tax rebates/other refunds', 'tax_rebates_other_refunds', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (7, 'Bequests (e.g. inheritance, gifts received)', 'Bequests_inheritance_gifts_received', false)");
        $this->addSql("INSERT INTO income_type (id, name, form_name, payment_description_required) VALUES (8, 'Other Income*', 'other_income', true)");
        
        //load expenditure type
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (1, 'Accommodation costs', 'accommodation_costs', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (2, 'Care fees/Local Authority charges for care', 'care_fees_local_authority_charges_for_care', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (3, 'Household bills', 'household_bills', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (4, 'Tax', 'tax', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (5, 'Insurance', 'insurance', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (6, 'Office of Public Guardian fees', 'office_of_public_guardian_fees', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (7, 'Deputy''s security bond premium', 'deputys_security_bond_premium', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (8, 'Capital expenditure/major purchases*', 'capital_expenditure_major_purchases', true)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (9, 'Property maintenance/improvement*', 'property_maintenance_improvement', true)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (10, 'Investments purchased*', 'investments_purchased', true)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (11, 'Transfers to other accounts/Court Funds', 'transfers_to_other_accounts_court_funds', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (12, 'Holidays/excursions', 'holidays_excursions', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (13, 'Professional fees', 'professional_fees', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (14, 'Deputy''s expenses*', 'deputys_expenses', true)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (15, 'Spending money provided for Client', 'spending_money_provided_for_client', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (16, 'Day to day living costs', 'day_to_day_living_costs', false)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (17, 'Gifts*', 'gifts', true)");
        $this->addSql("INSERT INTO expenditure_type (id, name, form_name, payment_description_required) VALUES (18, 'Other Expense*', 'other_expense', true)");
        
        $this->addSql("INSERT INTO config (cleanup) VALUES (false);");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
         $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
         
         $this->addSql("DELETE FROM expenditure_type");
         $this->addSql("DELETE FROM income_type");
         $this->addSql("DELETE FROM benefit_type");
         $this->addSql("DELETE FROM title");
         $this->addSql("DELETE FROM config");
         $this->addSql("DELETE FROM role");
         $this->addSql("DELETE FROM dd_user");
         $this->addSql("DELETE FROM court_order_type");
    }
}

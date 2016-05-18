<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE access_token (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6A2DD685F37A13B ON access_token (token)');
        $this->addSql('CREATE INDEX IDX_B6A2DD685DF1BCB8 ON access_token (oauth2_client_id)');
        $this->addSql('CREATE INDEX IDX_B6A2DD68A76ED395 ON access_token (user_id)');
        $this->addSql('CREATE TABLE account (id SERIAL NOT NULL, report_id INT DEFAULT NULL, bank_name VARCHAR(100) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, opening_balance NUMERIC(14, 2) DEFAULT NULL, opening_date_explanation TEXT DEFAULT NULL, closing_balance NUMERIC(14, 2) DEFAULT NULL, closing_balance_explanation TEXT DEFAULT NULL, opening_date DATE DEFAULT NULL, closing_date DATE DEFAULT NULL, closing_date_explanation TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D3656A44BD2A4C0 ON account (report_id)');
        $this->addSql('CREATE TABLE account_transaction (id SERIAL NOT NULL, account_id INT DEFAULT NULL, account_transaction_type_id VARCHAR(255) DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A370F9D29B6B5FBA ON account_transaction (account_id)');
        $this->addSql('CREATE INDEX IDX_A370F9D2387F8B02 ON account_transaction (account_transaction_type_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_trans ON account_transaction (account_id, account_transaction_type_id)');
        $this->addSql('CREATE TABLE account_transaction_type (id VARCHAR(255) NOT NULL, has_more_details BOOLEAN NOT NULL, display_order INT DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE asset (id SERIAL NOT NULL, report_id INT DEFAULT NULL, description TEXT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, valuation_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2AF5A5C4BD2A4C0 ON asset (report_id)');
        $this->addSql('CREATE TABLE audit_log_entry (id SERIAL NOT NULL, performed_by_user_id INT DEFAULT NULL, user_edited_id INT DEFAULT NULL, performed_by_user_name VARCHAR(150) NOT NULL, performed_by_user_email VARCHAR(150) NOT NULL, ip_address VARCHAR(15) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(15) NOT NULL, user_edited_name VARCHAR(150) DEFAULT NULL, user_edited_email VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2D938A243F2ED96 ON audit_log_entry (performed_by_user_id)');
        $this->addSql('CREATE INDEX IDX_D2D938A256B7314A ON audit_log_entry (user_edited_id)');
        $this->addSql('CREATE TABLE auth_code (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri TEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5933D02C5F37A13B ON auth_code (token)');
        $this->addSql('CREATE INDEX IDX_5933D02C5DF1BCB8 ON auth_code (oauth2_client_id)');
        $this->addSql('CREATE INDEX IDX_5933D02CA76ED395 ON auth_code (user_id)');
        $this->addSql('CREATE TABLE client (id SERIAL NOT NULL, case_number VARCHAR(20) DEFAULT NULL, email VARCHAR(60) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) DEFAULT NULL, allowed_court_order_types TEXT DEFAULT NULL, court_date DATE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN client.allowed_court_order_types IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE deputy_case (client_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(client_id, user_id))');
        $this->addSql('CREATE INDEX IDX_7F52717019EB6921 ON deputy_case (client_id)');
        $this->addSql('CREATE INDEX IDX_7F527170A76ED395 ON deputy_case (user_id)');
        $this->addSql('CREATE TABLE contact (id SERIAL NOT NULL, report_id INT DEFAULT NULL, contact_name VARCHAR(255) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, explanation TEXT DEFAULT NULL, relationship VARCHAR(100) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4C62E6384BD2A4C0 ON contact (report_id)');
        $this->addSql('CREATE TABLE court_order_type (id SERIAL NOT NULL, name VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE decision (id SERIAL NOT NULL, report_id INT DEFAULT NULL, description TEXT NOT NULL, client_involved_boolean BOOLEAN NOT NULL, client_involved_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84ACBE484BD2A4C0 ON decision (report_id)');
        $this->addSql('CREATE TABLE oauth2_client (id INT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris TEXT NOT NULL, secret VARCHAR(255) NOT NULL, allowed_grant_types TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN oauth2_client.redirect_uris IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN oauth2_client.allowed_grant_types IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE refresh_token (id INT NOT NULL, oauth2_client_id INT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74F21955F37A13B ON refresh_token (token)');
        $this->addSql('CREATE INDEX IDX_C74F21955DF1BCB8 ON refresh_token (oauth2_client_id)');
        $this->addSql('CREATE INDEX IDX_C74F2195A76ED395 ON refresh_token (user_id)');
        $this->addSql('CREATE TABLE report (id SERIAL NOT NULL, client_id INT DEFAULT NULL, court_order_type_id INT DEFAULT NULL, title VARCHAR(150) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, further_information TEXT DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT \'false\' NOT NULL, reason_for_no_contacts TEXT DEFAULT NULL, reason_for_no_decisions TEXT DEFAULT NULL, submitted BOOLEAN DEFAULT NULL, reviewed BOOLEAN DEFAULT NULL, report_seen BOOLEAN DEFAULT \'true\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F778419EB6921 ON report (client_id)');
        $this->addSql('CREATE INDEX IDX_C42F7784A47AEB9 ON report (court_order_type_id)');
        $this->addSql('CREATE TABLE role (id SERIAL NOT NULL, name VARCHAR(60) NOT NULL, role VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dd_user (id SERIAL NOT NULL, role_id INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) DEFAULT NULL, password VARCHAR(100) NOT NULL, email VARCHAR(60) NOT NULL, active BOOLEAN DEFAULT \'false\', salt VARCHAR(100) DEFAULT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, registration_token VARCHAR(100) DEFAULT NULL, email_confirmed BOOLEAN DEFAULT NULL, token_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, phone_main VARCHAR(20) DEFAULT NULL, phone_alternative VARCHAR(20) DEFAULT NULL, last_logged_in TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6764AB8BE7927C74 ON dd_user (email)');
        $this->addSql('CREATE INDEX IDX_6764AB8BD60322AC ON dd_user (role_id)');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD685DF1BCB8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A44BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_transaction ADD CONSTRAINT FK_A370F9D29B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_transaction ADD CONSTRAINT FK_A370F9D2387F8B02 FOREIGN KEY (account_transaction_type_id) REFERENCES account_transaction_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A243F2ED96 FOREIGN KEY (performed_by_user_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A256B7314A FOREIGN KEY (user_edited_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE auth_code ADD CONSTRAINT FK_5933D02C5DF1BCB8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE auth_code ADD CONSTRAINT FK_5933D02CA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F52717019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F527170A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE484BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F21955DF1BCB8 FOREIGN KEY (oauth2_client_id) REFERENCES oauth2_client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A47AEB9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        // SEQUENCES (all start from 1)
//        $this->addSql('CREATE SEQUENCE access_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE auth_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE oauth2_client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE refresh_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE account_transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE audit_log_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE court_order_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE decision_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
//        $this->addSql('CREATE SEQUENCE dd_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

         // DATA
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("INSERT INTO court_order_type (name) VALUES ('Personal Welfare'), ('Property and Affairs')");
        $this->addSql("INSERT INTO role (name, role) VALUES ('OPG Administrator', 'ROLE_ADMIN'), ('Lay Deputy', 'ROLE_LAY_DEPUTY'),('Professional Deputy', 'ROLE_PROFESSIONAL_DEPUTY'),('Local Authority Deputy', 'ROLE_LOCAL_AUTHORITY_DEPUTY' )");

        // TRANSACTIONS
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
            [12, 'in', 'income_from_investments_property_or_dividends', false],
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
            [9, 'out', 'insurance_eg_life_home_and_contents', false],
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
            [21, 'out', 'any_other_money_paid_out_and_not_listed_above', true],
        ];
        foreach ($rows as $row) {
            list($displayOrder, $type, $id, $hasMoreDetails) = $row;
            $hasMoreDetailsBool = $hasMoreDetails ? 'true' : 'false';
            $sql = "INSERT INTO account_transaction_type (display_order, type, id, has_more_details) 
                  VALUES('$displayOrder', '$type', '$id', '$hasMoreDetailsBool')";
            $this->addSql($sql);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account_transaction DROP CONSTRAINT FK_A370F9D29B6B5FBA');
        $this->addSql('ALTER TABLE account_transaction DROP CONSTRAINT FK_A370F9D2387F8B02');
        $this->addSql('ALTER TABLE deputy_case DROP CONSTRAINT FK_7F52717019EB6921');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F778419EB6921');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784A47AEB9');
        $this->addSql('ALTER TABLE access_token DROP CONSTRAINT FK_B6A2DD685DF1BCB8');
        $this->addSql('ALTER TABLE auth_code DROP CONSTRAINT FK_5933D02C5DF1BCB8');
        $this->addSql('ALTER TABLE refresh_token DROP CONSTRAINT FK_C74F21955DF1BCB8');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A44BD2A4C0');
        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5C4BD2A4C0');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E6384BD2A4C0');
        $this->addSql('ALTER TABLE decision DROP CONSTRAINT FK_84ACBE484BD2A4C0');
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT FK_6764AB8BD60322AC');
        $this->addSql('ALTER TABLE access_token DROP CONSTRAINT FK_B6A2DD68A76ED395');
        $this->addSql('ALTER TABLE audit_log_entry DROP CONSTRAINT FK_D2D938A243F2ED96');
        $this->addSql('ALTER TABLE audit_log_entry DROP CONSTRAINT FK_D2D938A256B7314A');
        $this->addSql('ALTER TABLE auth_code DROP CONSTRAINT FK_5933D02CA76ED395');
        $this->addSql('ALTER TABLE deputy_case DROP CONSTRAINT FK_7F527170A76ED395');
        $this->addSql('ALTER TABLE refresh_token DROP CONSTRAINT FK_C74F2195A76ED395');

//        $this->addSql('DROP SEQUENCE access_token_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE auth_code_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE oauth2_client_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE refresh_token_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE access_token_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE auth_code_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE oauth2_client_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE refresh_token_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE account_transaction_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE asset_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE audit_log_entry_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE client_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE contact_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE court_order_type_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE decision_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE report_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
//        $this->addSql('DROP SEQUENCE dd_user_id_seq CASCADE');

        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE account_transaction');
        $this->addSql('DROP TABLE account_transaction_type');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE audit_log_entry');
        $this->addSql('DROP TABLE auth_code');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE deputy_case');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE court_order_type');
        $this->addSql('DROP TABLE decision');
        $this->addSql('DROP TABLE oauth2_client');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE dd_user');
    }
}

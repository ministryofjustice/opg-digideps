<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version073 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE money_transfer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account (id SERIAL NOT NULL, report_id INT DEFAULT NULL, bank_name VARCHAR(500) DEFAULT NULL, account_type VARCHAR(125) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, opening_balance NUMERIC(14, 2) DEFAULT NULL, opening_date_explanation TEXT DEFAULT NULL, closing_balance NUMERIC(14, 2) DEFAULT NULL, closing_balance_explanation TEXT DEFAULT NULL, is_closed BOOLEAN NOT NULL, opening_date DATE DEFAULT NULL, closing_date DATE DEFAULT NULL, closing_date_explanation TEXT DEFAULT NULL, is_joint_account VARCHAR(3) DEFAULT NULL, meta TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D3656A44BD2A4C0 ON account (report_id)');
        $this->addSql('CREATE TABLE action (id SERIAL NOT NULL, report_id INT DEFAULT NULL, do_you_expect_decisions VARCHAR(4) DEFAULT NULL, do_you_expect_decisions_details TEXT DEFAULT NULL, do_you_have_concerns VARCHAR(4) DEFAULT NULL, do_you_have_concerns_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_47CC8C924BD2A4C0 ON action (report_id)');
        $this->addSql('CREATE TABLE asset (id SERIAL NOT NULL, report_id INT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, occupants VARCHAR(550) DEFAULT NULL, owned VARCHAR(15) DEFAULT NULL, owned_percentage NUMERIC(14, 2) DEFAULT NULL, is_subject_equity_rel VARCHAR(4) DEFAULT NULL, has_mortgage VARCHAR(4) DEFAULT NULL, mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL, has_charges VARCHAR(4) DEFAULT NULL, is_rented_out VARCHAR(4) DEFAULT NULL, rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rent_income_month NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, valuation_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2AF5A5C4BD2A4C0 ON asset (report_id)');
        $this->addSql('CREATE TABLE audit_log_entry (id SERIAL NOT NULL, performed_by_user_id INT DEFAULT NULL, user_edited_id INT DEFAULT NULL, performed_by_user_name VARCHAR(150) NOT NULL, performed_by_user_email VARCHAR(150) NOT NULL, ip_address VARCHAR(15) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(15) NOT NULL, user_edited_name VARCHAR(150) DEFAULT NULL, user_edited_email VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2D938A243F2ED96 ON audit_log_entry (performed_by_user_id)');
        $this->addSql('CREATE INDEX IDX_D2D938A256B7314A ON audit_log_entry (user_edited_id)');
        $this->addSql('CREATE TABLE casrec (id SERIAL NOT NULL, client_case_number VARCHAR(20) NOT NULL, client_lastname VARCHAR(50) NOT NULL, deputy_no VARCHAR(100) NOT NULL, deputy_lastname VARCHAR(100) DEFAULT NULL, deputy_postcode VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
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
        $this->addSql('CREATE TABLE money_transfer (id INT NOT NULL, from_account_id INT DEFAULT NULL, to_account_id INT DEFAULT NULL, report_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A15E50EEB0CF99BD ON money_transfer (from_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EEBC58BDC7 ON money_transfer (to_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EE4BD2A4C0 ON money_transfer (report_id)');
        $this->addSql('CREATE TABLE report (id SERIAL NOT NULL, client_id INT DEFAULT NULL, court_order_type_id INT DEFAULT NULL, title VARCHAR(150) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, further_information TEXT DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT \'false\', no_transfers_to_add BOOLEAN DEFAULT \'false\', reason_for_no_contacts TEXT DEFAULT NULL, reason_for_no_decisions TEXT DEFAULT NULL, submitted BOOLEAN DEFAULT NULL, reviewed BOOLEAN DEFAULT NULL, report_seen BOOLEAN DEFAULT \'true\' NOT NULL, all_agreed BOOLEAN DEFAULT NULL, reason_not_all_agreed TEXT DEFAULT NULL, balance_mismatch_explanation TEXT DEFAULT NULL, agreed_behalf_deputy VARCHAR(50) DEFAULT NULL, agreed_behalf_deputy_explanation TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F778419EB6921 ON report (client_id)');
        $this->addSql('CREATE INDEX IDX_C42F7784A47AEB9 ON report (court_order_type_id)');
        $this->addSql('CREATE TABLE role (id SERIAL NOT NULL, name VARCHAR(60) NOT NULL, role VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE safeguarding (id SERIAL NOT NULL, report_id INT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_contact_client TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C7877184BD2A4C0 ON safeguarding (report_id)');
        $this->addSql('CREATE TABLE transaction (id SERIAL NOT NULL, report_id INT DEFAULT NULL, transaction_type_id VARCHAR(255) DEFAULT NULL, amounts TEXT DEFAULT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_723705D14BD2A4C0 ON transaction (report_id)');
        $this->addSql('CREATE INDEX IDX_723705D1B3E6B071 ON transaction (transaction_type_id)');
        $this->addSql('CREATE UNIQUE INDEX report_unique_trans ON transaction (report_id, transaction_type_id)');
        $this->addSql('COMMENT ON COLUMN transaction.amounts IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE transaction_type (id VARCHAR(255) NOT NULL, has_more_details BOOLEAN NOT NULL, display_order INT DEFAULT NULL, category VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dd_user (id SERIAL NOT NULL, role_id INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) DEFAULT NULL, password VARCHAR(100) NOT NULL, email VARCHAR(60) NOT NULL, active BOOLEAN DEFAULT \'false\', salt VARCHAR(100) DEFAULT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, registration_token VARCHAR(100) DEFAULT NULL, email_confirmed BOOLEAN DEFAULT NULL, token_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, phone_main VARCHAR(20) DEFAULT NULL, phone_alternative VARCHAR(20) DEFAULT NULL, last_logged_in TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deputy_no VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6764AB8BE7927C74 ON dd_user (email)');
        $this->addSql('CREATE INDEX IDX_6764AB8BD60322AC ON dd_user (role_id)');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A44BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT FK_47CC8C924BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A243F2ED96 FOREIGN KEY (performed_by_user_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A256B7314A FOREIGN KEY (user_edited_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F52717019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F527170A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE484BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEB0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEBC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EE4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A47AEB9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE safeguarding ADD CONSTRAINT FK_8C7877184BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1B3E6B071 FOREIGN KEY (transaction_type_id) REFERENCES transaction_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

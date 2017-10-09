<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version144 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE money_transfer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE odr_asset (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, occupants VARCHAR(550) DEFAULT NULL, owned VARCHAR(15) DEFAULT NULL, owned_percentage NUMERIC(14, 2) DEFAULT NULL, is_subject_equity_rel VARCHAR(4) DEFAULT NULL, has_mortgage VARCHAR(4) DEFAULT NULL, mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL, has_charges VARCHAR(4) DEFAULT NULL, is_rented_out VARCHAR(4) DEFAULT NULL, rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rent_income_month NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, valuation_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73D022FB7CE4B994 ON odr_asset (odr_id)');
        $this->addSql('CREATE TABLE odr_account (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, bank_name VARCHAR(500) DEFAULT NULL, account_type VARCHAR(125) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, balance_on_cod NUMERIC(14, 2) DEFAULT NULL, is_joint_account VARCHAR(3) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C2AEF4FB7CE4B994 ON odr_account (odr_id)');
        $this->addSql('CREATE TABLE odr_debt (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, debt_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, has_more_details BOOLEAN NOT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_154224C77CE4B994 ON odr_debt (odr_id)');
        $this->addSql('CREATE TABLE odr (id SERIAL NOT NULL, client_id INT DEFAULT NULL, has_debts VARCHAR(5) DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT \'false\', submitted BOOLEAN DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, agreed_behalf_deputy VARCHAR(50) DEFAULT NULL, agreed_behalf_deputy_explanation TEXT DEFAULT NULL, receive_state_pension TEXT DEFAULT NULL, receive_other_income TEXT DEFAULT NULL, receive_other_income_details TEXT DEFAULT NULL, expect_compensation_damages TEXT DEFAULT NULL, expect_compensation_damages_details TEXT DEFAULT NULL, paid_for_anything VARCHAR(3) DEFAULT NULL, action_give_gifts_to_client VARCHAR(3) DEFAULT NULL, action_give_gifts_to_client_details TEXT DEFAULT NULL, action_property_maintenance VARCHAR(3) DEFAULT NULL, action_property_selling_rent VARCHAR(3) DEFAULT NULL, action_property_buy VARCHAR(3) DEFAULT NULL, action_more_info VARCHAR(3) DEFAULT NULL, action_more_info_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_350EBBC19EB6921 ON odr (client_id)');
        $this->addSql('CREATE TABLE odr_income_one_off (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5941831F7CE4B994 ON odr_income_one_off (odr_id)');
        $this->addSql('CREATE TABLE odr_expense (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92A22FF97CE4B994 ON odr_expense (odr_id)');
        $this->addSql('CREATE TABLE odr_visits_care (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, plan_move_residence VARCHAR(4) DEFAULT NULL, plan_move_residence_details TEXT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_contact_client TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9239DE877CE4B994 ON odr_visits_care (odr_id)');
        $this->addSql('CREATE TABLE odr_income_state_benefit (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C1A04A77CE4B994 ON odr_income_state_benefit (odr_id)');
        $this->addSql('CREATE TABLE casrec (id SERIAL NOT NULL, client_case_number VARCHAR(20) NOT NULL, client_lastname VARCHAR(50) NOT NULL, deputy_no VARCHAR(100) NOT NULL, deputy_lastname VARCHAR(100) DEFAULT NULL, deputy_postcode VARCHAR(10) DEFAULT NULL, type_of_report VARCHAR(10) DEFAULT NULL, corref VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE note (id SERIAL NOT NULL, client_id INT DEFAULT NULL, created_by INT DEFAULT NULL, last_modified_by INT DEFAULT NULL, category VARCHAR(100) NOT NULL, title VARCHAR(150) NOT NULL, content TEXT NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_note_client_id ON note (client_id)');
        $this->addSql('CREATE INDEX ix_note_created_by ON note (created_by)');
        $this->addSql('CREATE INDEX ix_note_last_modified_by ON note (last_modified_by)');
        $this->addSql('CREATE TABLE client (id SERIAL NOT NULL, case_number VARCHAR(20) DEFAULT NULL, email VARCHAR(60) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) DEFAULT NULL, court_date DATE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, date_of_birth DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX case_number_idx ON client (case_number)');
        $this->addSql('CREATE TABLE deputy_case (client_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(client_id, user_id))');
        $this->addSql('CREATE INDEX IDX_7F52717019EB6921 ON deputy_case (client_id)');
        $this->addSql('CREATE INDEX IDX_7F527170A76ED395 ON deputy_case (user_id)');
        $this->addSql('CREATE TABLE dd_team (id SERIAL NOT NULL, team_name VARCHAR(50) DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dd_user (id SERIAL NOT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) DEFAULT NULL, password VARCHAR(100) NOT NULL, email VARCHAR(60) NOT NULL, active BOOLEAN DEFAULT \'false\', salt VARCHAR(100) DEFAULT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, registration_token VARCHAR(100) DEFAULT NULL, token_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, role_name VARCHAR(50) DEFAULT NULL, phone_main VARCHAR(20) DEFAULT NULL, phone_alternative VARCHAR(20) DEFAULT NULL, last_logged_in TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deputy_no VARCHAR(100) DEFAULT NULL, odr_enabled BOOLEAN DEFAULT \'false\', ad_managed BOOLEAN DEFAULT \'false\', job_title VARCHAR(150) DEFAULT NULL, agree_terms_use BOOLEAN DEFAULT \'false\', agree_terms_use_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6764AB8BE7927C74 ON dd_user (email)');
        $this->addSql('CREATE TABLE user_team (user_id INT NOT NULL, team_id INT NOT NULL, PRIMARY KEY(user_id, team_id))');
        $this->addSql('CREATE INDEX IDX_BE61EAD6A76ED395 ON user_team (user_id)');
        $this->addSql('CREATE INDEX IDX_BE61EAD6296CD8AE ON user_team (team_id)');
        $this->addSql('CREATE TABLE asset (id SERIAL NOT NULL, report_id INT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, occupants VARCHAR(550) DEFAULT NULL, owned VARCHAR(15) DEFAULT NULL, owned_percentage NUMERIC(14, 2) DEFAULT NULL, is_subject_equity_rel VARCHAR(4) DEFAULT NULL, has_mortgage VARCHAR(4) DEFAULT NULL, mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL, has_charges VARCHAR(4) DEFAULT NULL, is_rented_out VARCHAR(4) DEFAULT NULL, rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rent_income_month NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, valuation_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2AF5A5C4BD2A4C0 ON asset (report_id)');
        $this->addSql('CREATE TABLE account (id SERIAL NOT NULL, report_id INT DEFAULT NULL, bank_name VARCHAR(500) DEFAULT NULL, account_type VARCHAR(125) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, opening_balance NUMERIC(14, 2) DEFAULT NULL, closing_balance NUMERIC(14, 2) DEFAULT NULL, is_closed BOOLEAN NOT NULL, is_joint_account VARCHAR(3) DEFAULT NULL, meta TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D3656A44BD2A4C0 ON account (report_id)');
        $this->addSql('CREATE TABLE money_transfer (id INT NOT NULL, from_account_id INT DEFAULT NULL, to_account_id INT DEFAULT NULL, report_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A15E50EEB0CF99BD ON money_transfer (from_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EEBC58BDC7 ON money_transfer (to_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EE4BD2A4C0 ON money_transfer (report_id)');
        $this->addSql('CREATE TABLE money_transaction_short (id SERIAL NOT NULL, report_id INT DEFAULT NULL, amount NUMERIC(14, 2) NOT NULL, description TEXT DEFAULT NULL, date DATE DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E712D1F64BD2A4C0 ON money_transaction_short (report_id)');
        $this->addSql('CREATE TABLE debt (id SERIAL NOT NULL, report_id INT DEFAULT NULL, debt_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, has_more_details BOOLEAN NOT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DBBF0A834BD2A4C0 ON debt (report_id)');
        $this->addSql('CREATE TABLE document (id SERIAL NOT NULL, report_id INT DEFAULT NULL, report_submission_id INT DEFAULT NULL, created_by INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, storage_reference VARCHAR(512) DEFAULT NULL, is_report_pdf BOOLEAN DEFAULT \'false\' NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D8698A7668200843 ON document (report_submission_id)');
        $this->addSql('CREATE INDEX ix_document_report_id ON document (report_id)');
        $this->addSql('CREATE INDEX ix_document_created_by ON document (created_by)');
        $this->addSql('CREATE TABLE fee (id SERIAL NOT NULL, report_id INT DEFAULT NULL, fee_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_964964B54BD2A4C0 ON fee (report_id)');
        $this->addSql('CREATE TABLE report (id SERIAL NOT NULL, client_id INT DEFAULT NULL, submitted_by INT DEFAULT NULL, type VARCHAR(3) NOT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, submitted BOOLEAN DEFAULT NULL, report_seen BOOLEAN DEFAULT \'true\' NOT NULL, agreed_behalf_deputy VARCHAR(50) DEFAULT NULL, agreed_behalf_deputy_explanation TEXT DEFAULT NULL, metadata TEXT DEFAULT NULL, wish_to_provide_documentation VARCHAR(255) DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT \'false\', balance_mismatch_explanation TEXT DEFAULT NULL, reason_for_no_contacts TEXT DEFAULT NULL, reason_for_no_decisions TEXT DEFAULT NULL, paid_for_anything VARCHAR(3) DEFAULT NULL, gifts_exist VARCHAR(3) DEFAULT NULL, money_transactions_short_in_exist VARCHAR(3) DEFAULT NULL, money_transactions_short_out_exist VARCHAR(3) DEFAULT NULL, no_transfers_to_add BOOLEAN DEFAULT \'false\', action_more_info VARCHAR(3) DEFAULT NULL, action_more_info_details TEXT DEFAULT NULL, has_debts VARCHAR(5) DEFAULT NULL, reason_for_no_fees TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F778419EB6921 ON report (client_id)');
        $this->addSql('CREATE INDEX IDX_C42F7784641EE842 ON report (submitted_by)');
        $this->addSql('CREATE TABLE contact (id SERIAL NOT NULL, report_id INT DEFAULT NULL, contact_name VARCHAR(255) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, explanation TEXT DEFAULT NULL, relationship VARCHAR(100) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4C62E6384BD2A4C0 ON contact (report_id)');
        $this->addSql('CREATE TABLE gift (id SERIAL NOT NULL, report_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A47C990D4BD2A4C0 ON gift (report_id)');
        $this->addSql('CREATE TABLE expense (id SERIAL NOT NULL, report_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D3A8DA64BD2A4C0 ON expense (report_id)');
        $this->addSql('CREATE TABLE safeguarding (id SERIAL NOT NULL, report_id INT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_contact_client TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C7877184BD2A4C0 ON safeguarding (report_id)');
        $this->addSql('CREATE TABLE report_submission (id SERIAL NOT NULL, report_id INT DEFAULT NULL, archived_by INT DEFAULT NULL, created_by INT DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C84776C84BD2A4C0 ON report_submission (report_id)');
        $this->addSql('CREATE INDEX IDX_C84776C851B07D6D ON report_submission (archived_by)');
        $this->addSql('CREATE INDEX IDX_C84776C8DE12AB56 ON report_submission (created_by)');
        $this->addSql('CREATE TABLE money_short_category (id SERIAL NOT NULL, report_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_106370F74BD2A4C0 ON money_short_category (report_id)');
        $this->addSql('CREATE TABLE money_transaction (id SERIAL NOT NULL, report_id INT DEFAULT NULL, category VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D21254E24BD2A4C0 ON money_transaction (report_id)');
        $this->addSql('CREATE TABLE decision (id SERIAL NOT NULL, report_id INT DEFAULT NULL, description TEXT NOT NULL, client_involved_boolean BOOLEAN NOT NULL, client_involved_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84ACBE484BD2A4C0 ON decision (report_id)');
        $this->addSql('CREATE TABLE action (id SERIAL NOT NULL, report_id INT DEFAULT NULL, do_you_expect_decisions VARCHAR(4) DEFAULT NULL, do_you_expect_decisions_details TEXT DEFAULT NULL, do_you_have_concerns VARCHAR(4) DEFAULT NULL, do_you_have_concerns_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_47CC8C924BD2A4C0 ON action (report_id)');
        $this->addSql('CREATE TABLE mental_capacity (id SERIAL NOT NULL, report_id INT DEFAULT NULL, has_capacity_changed VARCHAR(25) DEFAULT NULL, has_capacity_changed_details TEXT DEFAULT NULL, mental_assessment_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9564F4954BD2A4C0 ON mental_capacity (report_id)');
        $this->addSql('ALTER TABLE odr_asset ADD CONSTRAINT FK_73D022FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_account ADD CONSTRAINT FK_C2AEF4FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_debt ADD CONSTRAINT FK_154224C77CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT FK_350EBBC19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_one_off ADD CONSTRAINT FK_5941831F7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_expense ADD CONSTRAINT FK_92A22FF97CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_visits_care ADD CONSTRAINT FK_9239DE877CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_state_benefit ADD CONSTRAINT FK_1C1A04A77CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1465CF370E FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F52717019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F527170A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT FK_BE61EAD6296CD8AE FOREIGN KEY (team_id) REFERENCES dd_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A44BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEB0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEBC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EE4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transaction_short ADD CONSTRAINT FK_E712D1F64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE debt ADD CONSTRAINT FK_DBBF0A834BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7668200843 FOREIGN KEY (report_submission_id) REFERENCES report_submission (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fee ADD CONSTRAINT FK_964964B54BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784641EE842 FOREIGN KEY (submitted_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA64BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE safeguarding ADD CONSTRAINT FK_8C7877184BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C84BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C851B07D6D FOREIGN KEY (archived_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C8DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_short_category ADD CONSTRAINT FK_106370F74BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transaction ADD CONSTRAINT FK_D21254E24BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE484BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE action ADD CONSTRAINT fk_281efba84bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mental_capacity ADD CONSTRAINT FK_9564F4954BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

    }
}

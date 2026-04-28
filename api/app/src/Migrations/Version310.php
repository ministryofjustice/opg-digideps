<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1275';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_submission DROP CONSTRAINT fk_c84776c8b7b86a31');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76b7b86a31');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT fk_8a8e0c13b7b86a31');
        $this->addSql('DROP SEQUENCE odr_income_state_benefit_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_asset_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_debt_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_expense_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_income_one_off_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_visits_care_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE odr_account_id_seq CASCADE');
        $this->addSql('ALTER TABLE odr_expense DROP CONSTRAINT fk_92a22ff97ce4b994');
        $this->addSql('ALTER TABLE odr_income_one_off DROP CONSTRAINT fk_5941831f7ce4b994');
        $this->addSql('ALTER TABLE odr DROP CONSTRAINT fk_350ebbc19eb6921');
        $this->addSql('ALTER TABLE odr DROP CONSTRAINT fk_350ebbc641ee842');
        $this->addSql('ALTER TABLE odr_income_state_benefit DROP CONSTRAINT fk_1c1a04a77ce4b994');
        $this->addSql('ALTER TABLE odr_visits_care DROP CONSTRAINT fk_9239de877ce4b994');
        $this->addSql('ALTER TABLE odr_debt DROP CONSTRAINT fk_154224c77ce4b994');
        $this->addSql('ALTER TABLE odr_account DROP CONSTRAINT fk_c2aef4fb7ce4b994');
        $this->addSql('ALTER TABLE odr_client_benefits_check DROP CONSTRAINT fk_1457b1b0b7b86a31');
        $this->addSql('ALTER TABLE odr_asset DROP CONSTRAINT fk_73d022fb7ce4b994');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf DROP CONSTRAINT fk_df89fa855064a0ff');
        $this->addSql('DROP TABLE odr_expense');
        $this->addSql('DROP TABLE odr_income_one_off');
        $this->addSql('DROP TABLE odr');
        $this->addSql('DROP TABLE odr_income_state_benefit');
        $this->addSql('DROP TABLE odr_visits_care');
        $this->addSql('DROP TABLE odr_debt');
        $this->addSql('DROP TABLE odr_account');
        $this->addSql('DROP TABLE odr_client_benefits_check');
        $this->addSql('DROP TABLE odr_asset');
        $this->addSql('DROP TABLE odr_income_received_on_clients_behalf');
        $this->addSql('ALTER TABLE dd_user DROP odr_enabled');
        $this->addSql('DROP INDEX idx_d8698a76b7b86a31');
        $this->addSql('ALTER TABLE document DROP ndr_id');
        $this->addSql('ALTER TABLE pre_registration DROP ndr');
        $this->addSql('DROP INDEX idx_c84776c8b7b86a31');
        $this->addSql('ALTER TABLE report_submission DROP ndr_id');
        $this->addSql('DROP INDEX uniq_8a8e0c13b7b86a31');
        $this->addSql('ALTER TABLE satisfaction DROP ndr_id');
        $this->addSql('ALTER TABLE staging.selectedcandidates DROP ndr_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE odr_income_state_benefit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_debt_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_expense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_income_one_off_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_visits_care_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE odr_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE odr_expense (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_92a22ff97ce4b994 ON odr_expense (odr_id)');
        $this->addSql('CREATE TABLE odr_income_one_off (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5941831f7ce4b994 ON odr_income_one_off (odr_id)');
        $this->addSql('CREATE TABLE odr (id SERIAL NOT NULL, client_id INT DEFAULT NULL, submitted_by INT DEFAULT NULL, debt_management TEXT DEFAULT NULL, has_debts VARCHAR(5) DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT false, submitted BOOLEAN DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, agreed_behalf_deputy VARCHAR(50) DEFAULT NULL, agreed_behalf_deputy_explanation TEXT DEFAULT NULL, receive_state_pension TEXT DEFAULT NULL, receive_other_income TEXT DEFAULT NULL, receive_other_income_details TEXT DEFAULT NULL, expect_compensation_damages TEXT DEFAULT NULL, expect_compensation_damages_details TEXT DEFAULT NULL, paid_for_anything VARCHAR(3) DEFAULT NULL, action_give_gifts_to_client VARCHAR(3) DEFAULT NULL, action_give_gifts_to_client_details TEXT DEFAULT NULL, action_property_maintenance VARCHAR(3) DEFAULT NULL, action_property_selling_rent VARCHAR(3) DEFAULT NULL, action_property_buy VARCHAR(3) DEFAULT NULL, action_more_info VARCHAR(3) DEFAULT NULL, action_more_info_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_350ebbc641ee842 ON odr (submitted_by)');
        $this->addSql('CREATE INDEX odr_submit_date_idx ON odr (submit_date)');
        $this->addSql('CREATE INDEX odr_submitted_idx ON odr (submitted)');
        $this->addSql('CREATE UNIQUE INDEX uniq_350ebbc19eb6921 ON odr (client_id)');
        $this->addSql('CREATE TABLE odr_income_state_benefit (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1c1a04a77ce4b994 ON odr_income_state_benefit (odr_id)');
        $this->addSql('CREATE TABLE odr_visits_care (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, plan_move_residence VARCHAR(4) DEFAULT NULL, plan_move_residence_details TEXT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_contact_client TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_9239de877ce4b994 ON odr_visits_care (odr_id)');
        $this->addSql('CREATE TABLE odr_debt (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, debt_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, has_more_details BOOLEAN NOT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_154224c77ce4b994 ON odr_debt (odr_id)');
        $this->addSql('CREATE TABLE odr_account (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, bank_name VARCHAR(500) DEFAULT NULL, account_type VARCHAR(125) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, balance_on_cod NUMERIC(14, 2) DEFAULT NULL, is_joint_account VARCHAR(3) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_c2aef4fb7ce4b994 ON odr_account (odr_id)');
        $this->addSql('CREATE TABLE odr_client_benefits_check (id UUID NOT NULL, ndr_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, when_last_checked_entitlement VARCHAR(255) NOT NULL, date_last_checked_entitlement TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, never_checked_explanation TEXT DEFAULT NULL, do_others_receive_money_on_clients_behalf VARCHAR(255) DEFAULT NULL, dont_know_money_explanation TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_1457b1b0b7b86a31 ON odr_client_benefits_check (ndr_id)');
        $this->addSql('COMMENT ON COLUMN odr_client_benefits_check.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE odr_asset (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, occupants VARCHAR(550) DEFAULT NULL, owned VARCHAR(15) DEFAULT NULL, owned_percentage NUMERIC(14, 2) DEFAULT NULL, is_subject_equity_rel VARCHAR(4) DEFAULT NULL, has_mortgage VARCHAR(4) DEFAULT NULL, mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL, has_charges VARCHAR(4) DEFAULT NULL, is_rented_out VARCHAR(4) DEFAULT NULL, rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rent_income_month NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, valuation_date DATE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_73d022fb7ce4b994 ON odr_asset (odr_id)');
        $this->addSql('CREATE TABLE odr_income_received_on_clients_behalf (id UUID NOT NULL, client_benefits_check_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, money_type VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, who_received_money VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_df89fa855064a0ff ON odr_income_received_on_clients_behalf (client_benefits_check_id)');
        $this->addSql('COMMENT ON COLUMN odr_income_received_on_clients_behalf.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN odr_income_received_on_clients_behalf.client_benefits_check_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE odr_expense ADD CONSTRAINT fk_92a22ff97ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_one_off ADD CONSTRAINT fk_5941831f7ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT fk_350ebbc19eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT fk_350ebbc641ee842 FOREIGN KEY (submitted_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_state_benefit ADD CONSTRAINT fk_1c1a04a77ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_visits_care ADD CONSTRAINT fk_9239de877ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_debt ADD CONSTRAINT fk_154224c77ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_account ADD CONSTRAINT fk_c2aef4fb7ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_client_benefits_check ADD CONSTRAINT fk_1457b1b0b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_asset ADD CONSTRAINT fk_73d022fb7ce4b994 FOREIGN KEY (odr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ADD CONSTRAINT fk_df89fa855064a0ff FOREIGN KEY (client_benefits_check_id) REFERENCES odr_client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD odr_enabled BOOLEAN DEFAULT false');
        $this->addSql('ALTER TABLE pre_registration ADD ndr BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE report_submission ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT fk_c84776c8b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c84776c8b7b86a31 ON report_submission (ndr_id)');
        $this->addSql('ALTER TABLE document ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d8698a76b7b86a31 ON document (ndr_id)');
        $this->addSql('ALTER TABLE satisfaction ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT fk_8a8e0c13b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8a8e0c13b7b86a31 ON satisfaction (ndr_id)');
    }
}

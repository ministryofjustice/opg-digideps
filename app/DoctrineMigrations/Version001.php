<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version001 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // SCHEMA
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE account (id SERIAL NOT NULL, report_id INT DEFAULT NULL, bank_name VARCHAR(100) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, opening_balance NUMERIC(14, 2) DEFAULT NULL, closing_balance NUMERIC(14, 2) DEFAULT NULL, opening_date DATE DEFAULT NULL, closing_date DATE DEFAULT NULL, date_justification TEXT DEFAULT NULL, balance_justification TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7D3656A44BD2A4C0 ON account (report_id)');
        $this->addSql('CREATE TABLE asset (id SERIAL NOT NULL, report_id INT DEFAULT NULL, explanation TEXT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, p_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2AF5A5C4BD2A4C0 ON asset (report_id)');
        $this->addSql('CREATE TABLE benefit (id SERIAL NOT NULL, account_id INT DEFAULT NULL, benefit_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C8B001F9B6B5FBA ON benefit (account_id)');
        $this->addSql('CREATE INDEX IDX_5C8B001F9D0B88B8 ON benefit (benefit_type_id)');
        $this->addSql('CREATE TABLE benefit_payment (id SERIAL NOT NULL, benefit_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_356F8026B517B89 ON benefit_payment (benefit_id)');
        $this->addSql('CREATE TABLE benefit_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE client (id SERIAL NOT NULL, title_id INT DEFAULT NULL, court_order_type_id INT DEFAULT NULL, case_number VARCHAR(20) DEFAULT NULL, email VARCHAR(60) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) DEFAULT NULL, court_date DATE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7440455A9F87BD ON client (title_id)');
        $this->addSql('CREATE INDEX IDX_C7440455A47AEB9 ON client (court_order_type_id)');
        $this->addSql('CREATE TABLE deputy_case (client_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(client_id, user_id))');
        $this->addSql('CREATE INDEX IDX_7F52717019EB6921 ON deputy_case (client_id)');
        $this->addSql('CREATE INDEX IDX_7F527170A76ED395 ON deputy_case (user_id)');
        $this->addSql('CREATE TABLE config (id SERIAL NOT NULL, cleanup BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE contact (id SERIAL NOT NULL, title_id INT DEFAULT NULL, report_id INT DEFAULT NULL, firstname VARCHAR(50) DEFAULT NULL, lastname VARCHAR(50) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, explanation TEXT DEFAULT NULL, relationship VARCHAR(100) DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4C62E638A9F87BD ON contact (title_id)');
        $this->addSql('CREATE INDEX IDX_4C62E6384BD2A4C0 ON contact (report_id)');
        $this->addSql('CREATE TABLE court_order_type (id SERIAL NOT NULL, name VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE decision (id SERIAL NOT NULL, report_id INT DEFAULT NULL, decisions TEXT DEFAULT NULL, explanation TEXT DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, d_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84ACBE484BD2A4C0 ON decision (report_id)');
        $this->addSql('CREATE TABLE decision_involvement (id SERIAL NOT NULL, report_id INT DEFAULT NULL, involvement TEXT DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3495DDA4BD2A4C0 ON decision_involvement (report_id)');
        $this->addSql('CREATE TABLE expenditure (id SERIAL NOT NULL, account_id INT DEFAULT NULL, expenditure_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8D4A5FEB9B6B5FBA ON expenditure (account_id)');
        $this->addSql('CREATE INDEX IDX_8D4A5FEB5D9690AE ON expenditure (expenditure_type_id)');
        $this->addSql('CREATE TABLE expenditure_payment (id SERIAL NOT NULL, expenditure_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F5F2983068DC13E9 ON expenditure_payment (expenditure_id)');
        $this->addSql('CREATE TABLE expenditure_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE income (id SERIAL NOT NULL, account_id INT DEFAULT NULL, income_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3FA862D09B6B5FBA ON income (account_id)');
        $this->addSql('CREATE INDEX IDX_3FA862D07D0EFAEA ON income (income_type_id)');
        $this->addSql('CREATE TABLE income_payment (id SERIAL NOT NULL, income_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D56AF274640ED2C0 ON income_payment (income_id)');
        $this->addSql('CREATE TABLE income_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE pdf_token (id SERIAL NOT NULL, report_id INT DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEA0ECE14BD2A4C0 ON pdf_token (report_id)');
        $this->addSql('CREATE TABLE profile (id SERIAL NOT NULL, user_id INT DEFAULT NULL, title_id INT DEFAULT NULL, phone_home VARCHAR(20) DEFAULT NULL, phone_mobile VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, company VARCHAR(100) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, phone_work VARCHAR(20) DEFAULT NULL, trustcorp VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8157AA0FA76ED395 ON profile (user_id)');
        $this->addSql('CREATE INDEX IDX_8157AA0FA9F87BD ON profile (title_id)');
        $this->addSql('CREATE TABLE report (id SERIAL NOT NULL, client_id INT DEFAULT NULL, title VARCHAR(150) DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, further_information TEXT DEFAULT NULL, no_asset_to_add BOOLEAN DEFAULT \'false\' NOT NULL, no_contact_to_add BOOLEAN DEFAULT \'false\' NOT NULL, no_decision_to_add BOOLEAN DEFAULT \'false\' NOT NULL, submitted BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C42F778419EB6921 ON report (client_id)');
        $this->addSql('CREATE TABLE role (id SERIAL NOT NULL, name VARCHAR(60) NOT NULL, role VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE title (id SERIAL NOT NULL, title VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE dd_user (id SERIAL NOT NULL, role_id INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, password VARCHAR(300) NOT NULL, email VARCHAR(60) NOT NULL, active BOOLEAN DEFAULT \'false\', salt VARCHAR(100) DEFAULT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, registration_token VARCHAR(100) DEFAULT NULL, email_confirmed BOOLEAN DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, token_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6764AB8BD60322AC ON dd_user (role_id)');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A44BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE benefit ADD CONSTRAINT FK_5C8B001F9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE benefit ADD CONSTRAINT FK_5C8B001F9D0B88B8 FOREIGN KEY (benefit_type_id) REFERENCES benefit_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE benefit_payment ADD CONSTRAINT FK_356F8026B517B89 FOREIGN KEY (benefit_id) REFERENCES benefit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A9F87BD FOREIGN KEY (title_id) REFERENCES title (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455A47AEB9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F52717019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy_case ADD CONSTRAINT FK_7F527170A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E638A9F87BD FOREIGN KEY (title_id) REFERENCES title (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6384BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE484BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE decision_involvement ADD CONSTRAINT FK_3495DDA4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure ADD CONSTRAINT FK_8D4A5FEB9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure ADD CONSTRAINT FK_8D4A5FEB5D9690AE FOREIGN KEY (expenditure_type_id) REFERENCES expenditure_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure_payment ADD CONSTRAINT FK_F5F2983068DC13E9 FOREIGN KEY (expenditure_id) REFERENCES expenditure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D09B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_3FA862D07D0EFAEA FOREIGN KEY (income_type_id) REFERENCES income_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_payment ADD CONSTRAINT FK_D56AF274640ED2C0 FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pdf_token ADD CONSTRAINT FK_CEA0ECE14BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FA9F87BD FOREIGN KEY (title_id) REFERENCES title (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F778419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BD60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    
        
        // DATA
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("INSERT INTO court_order_type (name) VALUES ('Health & Welfare'), ('Property and Affairs')");
        $this->addSql("INSERT INTO court_order_type VALUES (3,'Property and Affairs & Personal Welfare')");
        $this->addSql("INSERT INTO title (title) VALUES ('Mr'),('Mrs'),('Miss'),('Ms'),('Dr'),('Professor'),('Sir'),('Dame'),('Lord'),('Baroness')");
        $this->addSql("INSERT INTO role (name, role) VALUES ('OPG Administrator', 'ROLE_ADMIN'), ('Lay Deputy', 'ROLE_LAY_DEPUTY'),('Professional Deputy', 'ROLE_PROFESSIONAL_DEPUTY'),('Local Authority Deputy', 'ROLE_LOCAL_AUTHORITY_DEPUTY' )");
   
        
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE benefit DROP CONSTRAINT FK_5C8B001F9B6B5FBA');
        $this->addSql('ALTER TABLE expenditure DROP CONSTRAINT FK_8D4A5FEB9B6B5FBA');
        $this->addSql('ALTER TABLE income DROP CONSTRAINT FK_3FA862D09B6B5FBA');
        $this->addSql('ALTER TABLE benefit_payment DROP CONSTRAINT FK_356F8026B517B89');
        $this->addSql('ALTER TABLE benefit DROP CONSTRAINT FK_5C8B001F9D0B88B8');
        $this->addSql('ALTER TABLE deputy_case DROP CONSTRAINT FK_7F52717019EB6921');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F778419EB6921');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455A47AEB9');
        $this->addSql('ALTER TABLE expenditure_payment DROP CONSTRAINT FK_F5F2983068DC13E9');
        $this->addSql('ALTER TABLE expenditure DROP CONSTRAINT FK_8D4A5FEB5D9690AE');
        $this->addSql('ALTER TABLE income_payment DROP CONSTRAINT FK_D56AF274640ED2C0');
        $this->addSql('ALTER TABLE income DROP CONSTRAINT FK_3FA862D07D0EFAEA');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A44BD2A4C0');
        $this->addSql('ALTER TABLE asset DROP CONSTRAINT FK_2AF5A5C4BD2A4C0');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E6384BD2A4C0');
        $this->addSql('ALTER TABLE decision DROP CONSTRAINT FK_84ACBE484BD2A4C0');
        $this->addSql('ALTER TABLE decision_involvement DROP CONSTRAINT FK_3495DDA4BD2A4C0');
        $this->addSql('ALTER TABLE pdf_token DROP CONSTRAINT FK_CEA0ECE14BD2A4C0');
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT FK_6764AB8BD60322AC');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455A9F87BD');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT FK_4C62E638A9F87BD');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0FA9F87BD');
        $this->addSql('ALTER TABLE deputy_case DROP CONSTRAINT FK_7F527170A76ED395');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0FA76ED395');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE benefit');
        $this->addSql('DROP TABLE benefit_payment');
        $this->addSql('DROP TABLE benefit_type');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE deputy_case');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE court_order_type');
        $this->addSql('DROP TABLE decision');
        $this->addSql('DROP TABLE decision_involvement');
        $this->addSql('DROP TABLE expenditure');
        $this->addSql('DROP TABLE expenditure_payment');
        $this->addSql('DROP TABLE expenditure_type');
        $this->addSql('DROP TABLE income');
        $this->addSql('DROP TABLE income_payment');
        $this->addSql('DROP TABLE income_type');
        $this->addSql('DROP TABLE pdf_token');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE title');
        $this->addSql('DROP TABLE dd_user');
    }
}

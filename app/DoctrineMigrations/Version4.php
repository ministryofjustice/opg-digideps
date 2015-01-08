<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version4 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('DROP SEQUENCE IF EXISTS benefit_type_other_btoid_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS contact_case_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS expenditure_type_other_etoid_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS income_type_other_itoid_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS profiles_pid_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS password_history_id_seq CASCADE');
        $this->addSql('DROP TABLE IF EXISTS benefit_type_other');
        $this->addSql('DROP TABLE IF EXISTS contact_case');
        $this->addSql('DROP TABLE IF EXISTS expenditure_type_other');
        $this->addSql('DROP TABLE IF EXISTS income_type_other');
        $this->addSql('DROP TABLE IF EXISTS profiles');
        $this->addSql('DROP TABLE IF EXISTS password_history');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('CREATE SEQUENCE benefit_type_other_btoid_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contact_case_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expenditure_type_other_etoid_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE income_type_other_itoid_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profiles_pid_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cases_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE court_order_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE decision_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE decision_involvement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expenditure_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expenditure_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE expenditure_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE income_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE income_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE income_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE password_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE pdf_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dd_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE title_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE benefit_type_other (btoid SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, PRIMARY KEY(btoid))');
        $this->addSql('CREATE TABLE contact_case (id SERIAL NOT NULL, nid INT DEFAULT NULL, cid INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE expenditure_type_other (etoid SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, PRIMARY KEY(etoid))');
        $this->addSql('CREATE TABLE income_type_other (itoid SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, PRIMARY KEY(itoid))');
        $this->addSql('CREATE TABLE profiles (pid SERIAL NOT NULL, uid INT NOT NULL, phone_home VARCHAR(20) DEFAULT NULL, phone_mobile VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, lastedit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(pid))');
        $this->addSql('CREATE TABLE password_history (id SERIAL NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_f352144a76ed395 ON password_history (user_id)');
        $this->addSql('ALTER TABLE password_history ADD CONSTRAINT fk_f352144a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

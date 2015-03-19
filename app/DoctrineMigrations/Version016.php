<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * delete old transaction tables
 */
class Version016 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE benefit DROP CONSTRAINT fk_5c8b001f9d0b88b8');
        $this->addSql('ALTER TABLE benefit_payment DROP CONSTRAINT fk_356f8026b517b89');
        $this->addSql('ALTER TABLE income DROP CONSTRAINT fk_3fa862d07d0efaea');
        $this->addSql('ALTER TABLE income_payment DROP CONSTRAINT fk_d56af274640ed2c0');
        $this->addSql('ALTER TABLE expenditure DROP CONSTRAINT fk_8d4a5feb5d9690ae');
        $this->addSql('ALTER TABLE expenditure_payment DROP CONSTRAINT fk_f5f2983068dc13e9');
        $this->addSql('DROP TABLE benefit_type');
        $this->addSql('DROP TABLE benefit');
        $this->addSql('DROP TABLE benefit_payment');
        $this->addSql('DROP TABLE income_type');
        $this->addSql('DROP TABLE income');
        $this->addSql('DROP TABLE income_payment');
        $this->addSql('DROP TABLE expenditure_type');
        $this->addSql('DROP TABLE expenditure');
        $this->addSql('DROP TABLE expenditure_payment');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE benefit_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE benefit (id SERIAL NOT NULL, account_id INT DEFAULT NULL, benefit_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_5c8b001f9d0b88b8 ON benefit (benefit_type_id)');
        $this->addSql('CREATE INDEX idx_5c8b001f9b6b5fba ON benefit (account_id)');
        $this->addSql('CREATE TABLE benefit_payment (id SERIAL NOT NULL, benefit_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_356f8026b517b89 ON benefit_payment (benefit_id)');
        $this->addSql('CREATE TABLE income_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE income (id SERIAL NOT NULL, account_id INT DEFAULT NULL, income_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_3fa862d09b6b5fba ON income (account_id)');
        $this->addSql('CREATE INDEX idx_3fa862d07d0efaea ON income (income_type_id)');
        $this->addSql('CREATE TABLE income_payment (id SERIAL NOT NULL, income_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d56af274640ed2c0 ON income_payment (income_id)');
        $this->addSql('CREATE TABLE expenditure_type (id SERIAL NOT NULL, name VARCHAR(60) DEFAULT NULL, form_name VARCHAR(60) DEFAULT NULL, payment_description_required BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE expenditure (id SERIAL NOT NULL, account_id INT DEFAULT NULL, expenditure_type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_8d4a5feb9b6b5fba ON expenditure (account_id)');
        $this->addSql('CREATE INDEX idx_8d4a5feb5d9690ae ON expenditure (expenditure_type_id)');
        $this->addSql('CREATE TABLE expenditure_payment (id SERIAL NOT NULL, expenditure_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(200) DEFAULT NULL, description VARCHAR(500) DEFAULT NULL, multiplier INT DEFAULT NULL, transaction_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_f5f2983068dc13e9 ON expenditure_payment (expenditure_id)');
        $this->addSql('ALTER TABLE benefit ADD CONSTRAINT fk_5c8b001f9b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE benefit ADD CONSTRAINT fk_5c8b001f9d0b88b8 FOREIGN KEY (benefit_type_id) REFERENCES benefit_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE benefit_payment ADD CONSTRAINT fk_356f8026b517b89 FOREIGN KEY (benefit_id) REFERENCES benefit (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT fk_3fa862d09b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT fk_3fa862d07d0efaea FOREIGN KEY (income_type_id) REFERENCES income_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_payment ADD CONSTRAINT fk_d56af274640ed2c0 FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure ADD CONSTRAINT fk_8d4a5feb9b6b5fba FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure ADD CONSTRAINT fk_8d4a5feb5d9690ae FOREIGN KEY (expenditure_type_id) REFERENCES expenditure_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE expenditure_payment ADD CONSTRAINT fk_f5f2983068dc13e9 FOREIGN KEY (expenditure_id) REFERENCES expenditure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

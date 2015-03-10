<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version002 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ADD court_order_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A47AEB9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C42F7784A47AEB9 ON report (court_order_type_id)');
        $this->addSql('ALTER TABLE dd_user ALTER password TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c7440455a47aeb9');
        $this->addSql('DROP INDEX idx_c7440455a47aeb9');
        $this->addSql('ALTER TABLE client ADD allowed_court_order_types TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE client DROP court_order_type_id');
        $this->addSql('COMMENT ON COLUMN client.allowed_court_order_types IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_payment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE benefit_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE client_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
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
        $this->addSql('CREATE SEQUENCE pdf_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE title_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dd_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE client ADD court_order_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client DROP allowed_court_order_types');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c7440455a47aeb9 FOREIGN KEY (court_order_type_id) REFERENCES court_order_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c7440455a47aeb9 ON client (court_order_type_id)');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784A47AEB9');
        $this->addSql('DROP INDEX IDX_C42F7784A47AEB9');
        $this->addSql('ALTER TABLE report DROP court_order_type_id');
        $this->addSql('ALTER TABLE dd_user ALTER password TYPE VARCHAR(300)');
    }
}

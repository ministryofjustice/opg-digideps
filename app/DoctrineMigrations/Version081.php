<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version081 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE odr_account (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, bank_name VARCHAR(500) DEFAULT NULL, account_type VARCHAR(125) DEFAULT NULL, sort_code VARCHAR(6) DEFAULT NULL, account_number VARCHAR(4) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, balance_on_cod NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C2AEF4FB7CE4B994 ON odr_account (odr_id)');
        $this->addSql('ALTER TABLE odr_account ADD CONSTRAINT FK_C2AEF4FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE odr_account');
    }
}

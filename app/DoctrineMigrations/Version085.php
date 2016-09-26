<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version085 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE odr_income_one_off (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5941831F7CE4B994 ON odr_income_one_off (odr_id)');
        $this->addSql('CREATE TABLE odr_income_state_benefit (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, present BOOLEAN DEFAULT NULL, has_more_details VARCHAR(255) NOT NULL, more_details VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1C1A04A77CE4B994 ON odr_income_state_benefit (odr_id)');
        $this->addSql('ALTER TABLE odr_income_one_off ADD CONSTRAINT FK_5941831F7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_state_benefit ADD CONSTRAINT FK_1C1A04A77CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD receive_state_pension TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD receive_other_income TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD receive_other_income_details TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD expect_compensation_damages TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD expect_compensation_damages_details TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE odr_income_one_off');
        $this->addSql('DROP TABLE odr_income_state_benefit');
        $this->addSql('ALTER TABLE odr DROP receive_state_pension');
        $this->addSql('ALTER TABLE odr DROP receive_other_income');
        $this->addSql('ALTER TABLE odr DROP receive_other_income_details');
        $this->addSql('ALTER TABLE odr DROP expect_compensation_damages');
        $this->addSql('ALTER TABLE odr DROP expect_compensation_damages_details');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version085 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE income_one_off (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7C6F98B97CE4B994 ON income_one_off (odr_id)');
        $this->addSql('ALTER TABLE income_one_off ADD CONSTRAINT FK_7C6F98B97CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD state_benefits TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD receive_state_pension TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD receive_other_income TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD expect_compensation TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN odr.state_benefits IS \'(DC2Type:array)\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE income_one_off');
        $this->addSql('ALTER TABLE odr DROP state_benefits');
        $this->addSql('ALTER TABLE odr DROP receive_state_pension');
        $this->addSql('ALTER TABLE odr DROP receive_other_income');
        $this->addSql('ALTER TABLE odr DROP expect_compensation');
    }
}

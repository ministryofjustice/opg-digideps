<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version086 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE expense (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, explanation TEXT NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D3A8DA67CE4B994 ON expense (odr_id)');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA67CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD paid_for_anything VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD planning_claim_expenses VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE odr ADD planning_claim_expenses_details TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE expense');
        $this->addSql('ALTER TABLE odr DROP paid_for_anything');
        $this->addSql('ALTER TABLE odr DROP planning_claim_expenses');
        $this->addSql('ALTER TABLE odr DROP planning_claim_expenses_details');
    }
}

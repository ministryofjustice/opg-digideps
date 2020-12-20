<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version188 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dd_team DROP address1');
        $this->addSql('ALTER TABLE dd_team DROP address2');
        $this->addSql('ALTER TABLE dd_team DROP address3');
        $this->addSql('ALTER TABLE dd_team DROP address_postcode');
        $this->addSql('ALTER TABLE dd_team DROP address_country');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dd_team ADD address1 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_team ADD address2 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_team ADD address3 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_team ADD address_postcode VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_team ADD address_country VARCHAR(10) DEFAULT NULL');
    }
}

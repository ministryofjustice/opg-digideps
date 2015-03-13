<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * new fields for user entity (taken from profile)
 */
class Version006 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dd_user ADD address1 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address2 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address3 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address_postcode VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address_country VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD phone_work VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD phone_home VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD phone_mobile VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE dd_user DROP address1');
        $this->addSql('ALTER TABLE dd_user DROP address2');
        $this->addSql('ALTER TABLE dd_user DROP address3');
        $this->addSql('ALTER TABLE dd_user DROP address_postcode');
        $this->addSql('ALTER TABLE dd_user DROP address_country');
        $this->addSql('ALTER TABLE dd_user DROP phone_work');
        $this->addSql('ALTER TABLE dd_user DROP phone_home');
        $this->addSql('ALTER TABLE dd_user DROP phone_mobile');
    }
}

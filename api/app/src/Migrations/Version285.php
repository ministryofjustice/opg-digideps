<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version285 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds columns to store the new client data from the daily import';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pre_registration ADD client_firstname VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address_1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address_2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address_3 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address_4 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address_5 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_postcode VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pre_registration DROP client_firstname');
        $this->addSql('ALTER TABLE pre_registration DROP client_address_1');
        $this->addSql('ALTER TABLE pre_registration DROP client_address_2');
        $this->addSql('ALTER TABLE pre_registration DROP client_address_3');
        $this->addSql('ALTER TABLE pre_registration DROP client_address_4');
        $this->addSql('ALTER TABLE pre_registration DROP client_address_5');
        $this->addSql('ALTER TABLE pre_registration DROP client_postcode');
    }
}

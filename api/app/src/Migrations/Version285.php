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
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pre_registration ADD client_firstname VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address1 VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address2 VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address3 VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address4 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_address5 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pre_registration ADD client_postcode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pre_registration RENAME COLUMN order_type TO court_order_type');
        $this->addSql('ALTER TABLE pre_registration RENAME COLUMN order_date TO court_order_date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pre_registration DROP client_firstname');
        $this->addSql('ALTER TABLE pre_registration DROP client_address1');
        $this->addSql('ALTER TABLE pre_registration DROP client_address2');
        $this->addSql('ALTER TABLE pre_registration DROP client_address3');
        $this->addSql('ALTER TABLE pre_registration DROP client_address4');
        $this->addSql('ALTER TABLE pre_registration DROP client_address5');
        $this->addSql('ALTER TABLE pre_registration DROP client_postcode');
        $this->addSql('ALTER TABLE pre_registration DROP court_order_type');
        $this->addSql('ALTER TABLE pre_registration RENAME COLUMN court_order_type TO order_type');
        $this->addSql('ALTER TABLE pre_registration RENAME COLUMN court_order_date TO order_date');
    }
}

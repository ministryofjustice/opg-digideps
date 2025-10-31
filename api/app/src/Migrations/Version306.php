<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order DROP CONSTRAINT fk_e824c0b7b86a31');
        $this->addSql('DROP INDEX idx_e824c0b7b86a31');
        $this->addSql('ALTER TABLE court_order DROP ndr_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT fk_e824c0b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_e824c0b7b86a31 ON court_order (ndr_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version264 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a created_at and updated_at column to reports, users, clients, and named deputy tables.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report ADD created_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD updated_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD created_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD updated_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD created_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD updated_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD created_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD updated_at TIMESTAMP NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP created_at');
        $this->addSql('ALTER TABLE report DROP updated_at');
        $this->addSql('ALTER TABLE dd_user DROP created_at');
        $this->addSql('ALTER TABLE dd_user DROP updated_at');
        $this->addSql('ALTER TABLE client DROP created_at');
        $this->addSql('ALTER TABLE client DROP updated_at');
        $this->addSql('ALTER TABLE named_deputy DROP created_at');
        $this->addSql('ALTER TABLE named_deputy DROP updated_at');
    }
}

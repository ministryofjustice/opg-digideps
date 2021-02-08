<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE checklist ADD synchronised_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD opg_uuid VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD synchronisation_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD synchronisation_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD synchronisation_error VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F1EFE0E05 FOREIGN KEY (synchronised_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5C696D2F1EFE0E05 ON checklist (synchronised_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE checklist DROP CONSTRAINT FK_5C696D2F1EFE0E05');
        $this->addSql('DROP INDEX IDX_5C696D2F1EFE0E05');
        $this->addSql('ALTER TABLE checklist DROP synchronised_by');
        $this->addSql('ALTER TABLE checklist DROP opg_uuid');
        $this->addSql('ALTER TABLE checklist DROP synchronisation_status');
        $this->addSql('ALTER TABLE checklist DROP synchronisation_time');
        $this->addSql('ALTER TABLE checklist DROP synchronisation_error');
    }
}

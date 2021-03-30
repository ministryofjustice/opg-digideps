<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document ADD synchronised_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD synchronisation_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD synchronisation_time TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD synchronisation_error VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A761EFE0E05 FOREIGN KEY (synchronised_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D8698A761EFE0E05 ON document (synchronised_by)');
        $this->addSql('ALTER TABLE report_submission ADD opg_uuid VARCHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A761EFE0E05');
        $this->addSql('DROP INDEX IDX_D8698A761EFE0E05');
        $this->addSql('ALTER TABLE document DROP synchronised_by');
        $this->addSql('ALTER TABLE document DROP synchronisation_status');
        $this->addSql('ALTER TABLE document DROP synchronisation_time');
        $this->addSql('ALTER TABLE document DROP synchronisation_error');
        $this->addSql('ALTER TABLE report_submission DROP opg_uuid');
    }
}

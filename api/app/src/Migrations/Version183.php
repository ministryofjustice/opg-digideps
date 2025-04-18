<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version183 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE checklist ADD last_modified_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F65CF370E FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5C696D2F65CF370E ON checklist (last_modified_by)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE checklist DROP CONSTRAINT FK_5C696D2F65CF370E');
        $this->addSql('DROP INDEX IDX_5C696D2F65CF370E');
        $this->addSql('ALTER TABLE checklist DROP last_modified_by');
        $this->addSql('ALTER TABLE checklist DROP last_modified_on');
    }
}

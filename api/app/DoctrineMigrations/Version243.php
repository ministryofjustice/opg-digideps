<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version243 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document ALTER synchronisation_error TYPE TEXT');
        $this->addSql('ALTER TABLE document ALTER synchronisation_error DROP DEFAULT');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error TYPE TEXT');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document ALTER synchronisation_error TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE document ALTER synchronisation_error DROP DEFAULT');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error DROP DEFAULT');
    }
}

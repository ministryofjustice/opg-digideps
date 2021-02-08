<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Increases the number of characters stored in sync errors';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE document ALTER synchronisation_error TYPE TEXT');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error TYPE TEXT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE document ALTER synchronisation_error TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE checklist ALTER synchronisation_error TYPE VARCHAR(255)');
    }
}

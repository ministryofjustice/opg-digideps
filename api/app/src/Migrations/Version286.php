<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version286 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjusts column on deputy table to match data elsewhere in database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy ALTER COLUMN deputy_uid TYPE BIGINT;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy ALTER COLUMN deputy_uid TYPE VARCHAR(20);');
    }
}

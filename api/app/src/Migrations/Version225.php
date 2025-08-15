<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change all Org names that do not contain an @ to "Your Organisation"';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE organisation SET name = \'Your Organisation\' WHERE name NOT LIKE \'%@%\'');
    }

    public function down(Schema $schema): void
    {
    }
}

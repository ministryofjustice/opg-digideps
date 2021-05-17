<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migrate ROLE_CASE_MANAGER users to ROLE_ADMIN';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('UPDATE dd_user SET role_name = \'ROLE_ADMIN\' WHERE role_name = \'ROLE_CASE_MANAGER\'');
    }

    public function down(Schema $schema): void
    {
    }
}

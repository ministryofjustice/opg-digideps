<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version291 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update court_order_uid data type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE court_order ALTER court_order_uid TYPE VARCHAR(36)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE court_order ALTER court_order_uid TYPE BIGINT');
    }
}

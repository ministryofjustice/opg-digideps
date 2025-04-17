<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version294 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE INDEX deputy_uid_idx ON staging.deputyship (deputy_uid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX order_uid_idx ON staging.deputyship (order_uid)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX deputy_uid_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX order_uid_idx
        SQL);
    }
}

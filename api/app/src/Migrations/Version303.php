<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_e824c0b7b86a31
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E824C0B7B86A31 ON court_order (ndr_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E824C0B7B86A31
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_e824c0b7b86a31 ON court_order (ndr_id)
        SQL);
    }
}

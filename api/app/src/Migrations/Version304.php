<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order DROP CONSTRAINT FK_E824C0B7B86A31
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order ADD CONSTRAINT FK_E824C0B7B86A31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order DROP CONSTRAINT fk_e824c0b7b86a31
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE court_order ADD CONSTRAINT fk_e824c0b7b86a31 FOREIGN KEY (ndr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}

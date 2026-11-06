<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1810';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dd_user DROP is_primary');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6764AB8B64B76B02 ON dd_user (deputy_uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6764AB8B64B76B02');
        $this->addSql('ALTER TABLE dd_user ADD is_primary BOOLEAN DEFAULT false NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deputy ADD deputy_type VARCHAR(3) NULL');
        $this->addSql('UPDATE deputy d SET deputy_type = COALESCE(ds.deputy_type, \'\') FROM staging.deputyship ds WHERE d.deputy_uid = ds.deputy_uid; ');
        $this->addSql('ALTER TABLE deputy ALTER deputy_type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deputy DROP deputy_type');
    }
}

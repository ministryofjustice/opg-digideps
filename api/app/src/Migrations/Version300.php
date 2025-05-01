<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedcandidates DROP is_hybrid
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedcandidates ALTER deputy_uid DROP NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedCandidates ADD is_hybrid VARCHAR(30) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedCandidates ALTER deputy_uid SET NOT NULL
        SQL);
    }
}

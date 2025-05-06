<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ndr_id field to stating selected candidates table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedcandidates ADD ndr_id INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.selectedCandidates DROP ndr_id
        SQL);
    }
}

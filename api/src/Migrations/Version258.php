<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add explanation column for clients benefits income question';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check ADD dont_know_income_explanation TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check DROP dont_know_income_explanation');
    }
}

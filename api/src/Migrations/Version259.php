<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds option to confirm client benefits check has been completed correctly';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE checklist ADD client_benefits_checked VARCHAR(3) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE checklist DROP client_benefits_checked');
    }
}

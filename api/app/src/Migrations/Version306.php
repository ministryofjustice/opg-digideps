<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove deputy_no field from dd_user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX deputy_no_idx');
        $this->addSql('ALTER TABLE dd_user DROP deputy_no');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dd_user ADD deputy_no VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE INDEX deputy_no_idx ON dd_user (deputy_no)');
    }
}

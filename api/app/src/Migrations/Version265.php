<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version265 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created by to User so we can track who creates users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dd_user ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BB03A8386 FOREIGN KEY (created_by_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT FK_6764AB8BB03A8386');
        $this->addSql('ALTER TABLE dd_user DROP created_by_id');
    }
}

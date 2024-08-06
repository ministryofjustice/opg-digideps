<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version273 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Recreate created_by constraint on dd_user to set to null on deletion of referenced user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT FK_6764AB8BB03A8386');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT DD_USER_CREATED_BY_FK FOREIGN KEY (created_by_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT DD_USER_CREATED_BY_FK');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BB03A8386 FOREIGN KEY (created_by_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version282 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds index for created_by_id column on dd_user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT dd_user_created_by_fk');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT FK_6764AB8BB03A8386 FOREIGN KEY (created_by_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6764AB8BB03A8386 ON dd_user (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dd_user DROP CONSTRAINT FK_6764AB8BB03A8386');
        $this->addSql('DROP INDEX UNIQ_6764AB8BB03A8386');
        $this->addSql('ALTER TABLE dd_user ADD CONSTRAINT dd_user_created_by_fk FOREIGN KEY (created_by_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

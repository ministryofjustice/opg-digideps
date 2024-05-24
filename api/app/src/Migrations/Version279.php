<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version279 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT FK_28FA6B9FA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28FA6B9FA76ED395 ON deputy (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT FK_28FA6B9FA76ED395');
        $this->addSql('DROP INDEX UNIQ_28FA6B9FA76ED395');
        $this->addSql('ALTER TABLE deputy DROP user_id');
    }
}

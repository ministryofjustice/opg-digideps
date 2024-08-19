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
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71aa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71aa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

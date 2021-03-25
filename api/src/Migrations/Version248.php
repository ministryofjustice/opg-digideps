<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version248 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE research_type (id UUID NOT NULL, surveys BOOLEAN DEFAULT NULL, video_call BOOLEAN DEFAULT NULL, phone BOOLEAN DEFAULT NULL, in_person BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN research_type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_research_response (id UUID NOT NULL, user_id INT DEFAULT NULL, deputyship_length VARCHAR(255) NOT NULL, has_access_to_video_call_device BOOLEAN NOT NULL, researchType_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B9FE71A5423F28F ON user_research_response (researchType_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B9FE71AA76ED395 ON user_research_response (user_id)');
        $this->addSql('COMMENT ON COLUMN user_research_response.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_research_response.researchType_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71A5423F28F FOREIGN KEY (researchType_id) REFERENCES research_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71AA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71A5423F28F');
        $this->addSql('DROP TABLE research_type');
        $this->addSql('DROP TABLE user_research_response');
    }
}

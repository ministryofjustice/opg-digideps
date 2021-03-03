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
        return 'Add UserResearchResponse and ResearchType tables to support UR feedback';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE research_type (id UUID NOT NULL, user_research_response_id INT NOT NULL, surveys BOOLEAN NOT NULL, video_call BOOLEAN NOT NULL, phone BOOLEAN NOT NULL, inPerson BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN research_type.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_research_response (id UUID NOT NULL, research_type_id INT NOT NULL, deputyship_length VARCHAR(255) NOT NULL, has_access_to_video_call_device BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN user_research_response.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE research_type');
        $this->addSql('DROP TABLE user_research_response');
    }
}

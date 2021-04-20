<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Linking UserResearchResponses to Satisfaction';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE satisfaction ADD userResearchResponse_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN satisfaction.userResearchResponse_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C13F03886A6 FOREIGN KEY (userResearchResponse_id) REFERENCES user_research_response (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8E0C13F03886A6 ON satisfaction (userResearchResponse_id)');
        $this->addSql('DROP INDEX uniq_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE user_research_response ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('CREATE INDEX IDX_3B9FE71AA76ED395 ON user_research_response (user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT FK_8A8E0C13F03886A6');
        $this->addSql('DROP INDEX UNIQ_8A8E0C13F03886A6');
        $this->addSql('ALTER TABLE satisfaction DROP userResearchResponse_id');
        $this->addSql('DROP INDEX IDX_3B9FE71AA76ED395');
        $this->addSql('ALTER TABLE user_research_response DROP created_at');
        $this->addSql('CREATE UNIQUE INDEX uniq_3b9fe71aa76ed395 ON user_research_response (user_id)');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Linking UserResearchResponses to Satisfaction and Satisfaction to Report & NDR';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('DROP INDEX uniq_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE satisfaction ADD report_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE satisfaction ADD ndr_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C13B7B86A31 FOREIGN KEY (ndr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD satisfaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_research_response ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71ADE9439B8 FOREIGN KEY (satisfaction_id) REFERENCES satisfaction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3B9FE71AA76ED395 ON user_research_response (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B9FE71ADE9439B8 ON user_research_response (satisfaction_id)');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C134BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8E0C134BD2A4C0 ON satisfaction (report_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8E0C13B7B86A31 ON satisfaction (ndr_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71ADE9439B8');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT FK_8A8E0C13B7B86A31');
        $this->addSql('DROP INDEX UNIQ_8A8E0C13B7B86A31');
        $this->addSql('DROP INDEX IDX_3B9FE71AA76ED395');
        $this->addSql('DROP INDEX UNIQ_3B9FE71ADE9439B8');
        $this->addSql('ALTER TABLE user_research_response DROP satisfaction_id');
        $this->addSql('ALTER TABLE user_research_response DROP created_at');
        $this->addSql('CREATE UNIQUE INDEX uniq_3b9fe71aa76ed395 ON user_research_response (user_id)');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT FK_8A8E0C134BD2A4C0');
        $this->addSql('DROP INDEX UNIQ_8A8E0C134BD2A4C0');
        $this->addSql('ALTER TABLE satisfaction DROP report_id');
        $this->addSql('ALTER TABLE satisfaction DROP ndr_id');
    }
}

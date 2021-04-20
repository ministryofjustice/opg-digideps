<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Linking Users to Satisfaction responses';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE satisfaction ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT FK_8A8E0C13A76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8A8E0C13A76ED395 ON satisfaction (user_id)');
        $this->addSql('DROP INDEX uniq_3b9fe71aa76ed395');
        $this->addSql('CREATE INDEX IDX_3B9FE71AA76ED395 ON user_research_response (user_id)');
        $this->addSql('ALTER TABLE user_research_response ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT FK_8A8E0C13A76ED395');
        $this->addSql('DROP INDEX IDX_8A8E0C13A76ED395');
        $this->addSql('ALTER TABLE satisfaction DROP user_id');
        $this->addSql('DROP INDEX IDX_3B9FE71AA76ED395');
        $this->addSql('CREATE UNIQUE INDEX uniq_3b9fe71aa76ed395 ON user_research_response (user_id)');
        $this->addSql('ALTER TABLE user_research_response DROP created_at');
    }
}

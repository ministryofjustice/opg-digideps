<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version292 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT FK_28FA6B9FA76ED395');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT FK_28FA6B9FA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ALTER sync_attempts SET DEFAULT 0');
        $this->addSql('ALTER TABLE pre_registration ALTER hybrid TYPE VARCHAR(12)');
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(100)');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71A5423F28F');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71AA76ED395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71A5423F28F FOREIGN KEY (researchType_id) REFERENCES research_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71AA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71a5423f28f');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71a5423f28f FOREIGN KEY (researchtype_id) REFERENCES research_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71aa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE pre_registration ALTER hybrid TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE document ALTER sync_attempts DROP DEFAULT');
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT fk_28fa6b9fa76ed395');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT fk_28fa6b9fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

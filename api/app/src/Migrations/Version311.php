<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX staging.deputy_uid_idx');
        $this->addSql('DROP INDEX staging.order_uid_idx');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71A5423F28F');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71A5423F28F FOREIGN KEY (researchType_id) REFERENCES research_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71a5423f28f');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71a5423f28f FOREIGN KEY (researchtype_id) REFERENCES research_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX deputy_uid_idx ON staging.deputyship (deputy_uid)');
        $this->addSql('CREATE INDEX order_uid_idx ON staging.deputyship (order_uid)');
    }
}

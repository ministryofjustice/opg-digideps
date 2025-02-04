<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version290 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align database structure with ORM entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_c744045595162e7c RENAME TO IDX_C74404554B6F93BB');
        $this->addSql('ALTER TABLE dd_user ALTER lastname SET NOT NULL');
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT FK_28FA6B9FA76ED395');
        $this->addSql('ALTER TABLE deputy ALTER deputy_uid SET NOT NULL');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT FK_28FA6B9FA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX uniq_1058689d625b8d2 RENAME TO UNIQ_28FA6B9F64B76B02');
        $this->addSql('ALTER TABLE document ALTER sync_attempts DROP DEFAULT');
        $this->addSql('ALTER TABLE money_transfer ALTER description TYPE TEXT');
        $this->addSql('ALTER TABLE pre_registration ALTER hybrid TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(10)');
        $this->addSql('ALTER INDEX updated_at_index RENAME TO updated_at_idx');
        $this->addSql('ALTER TABLE satisfaction ALTER comments TYPE VARCHAR(1200)');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71A5423F28F');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT FK_3B9FE71AA76ED395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71A5423F28F FOREIGN KEY (researchType_id) REFERENCES research_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT FK_3B9FE71AA76ED395 FOREIGN KEY (user_id) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ALTER sync_attempts SET DEFAULT 0');
        $this->addSql('ALTER TABLE dd_user ALTER lastname DROP NOT NULL');
        $this->addSql('ALTER TABLE money_transfer ALTER description TYPE VARCHAR(75)');
        $this->addSql('ALTER INDEX idx_c74404554b6f93bb RENAME TO idx_c744045595162e7c');
        $this->addSql('ALTER TABLE pre_registration ALTER client_firstname TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE pre_registration ALTER hybrid TYPE VARCHAR(6)');
        $this->addSql('ALTER INDEX updated_at_idx RENAME TO updated_at_index');
        $this->addSql('ALTER TABLE satisfaction ALTER comments TYPE TEXT');
        $this->addSql('ALTER TABLE satisfaction ALTER comments TYPE TEXT');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71a5423f28f');
        $this->addSql('ALTER TABLE user_research_response DROP CONSTRAINT fk_3b9fe71aa76ed395');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71a5423f28f FOREIGN KEY (researchtype_id) REFERENCES research_type (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_response ADD CONSTRAINT fk_3b9fe71aa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT fk_28fa6b9fa76ed395');
        $this->addSql('ALTER TABLE deputy ALTER deputy_uid DROP NOT NULL');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT fk_28fa6b9fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX uniq_28fa6b9f64b76b02 RENAME TO uniq_1058689d625b8d2');
    }
}

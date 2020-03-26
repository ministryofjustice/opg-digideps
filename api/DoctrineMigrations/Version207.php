<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version207 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE checklist ADD payments_match_cost_certificate VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD prof_costs_reasonable_and_proportionate VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE checklist ADD has_deputy_overcharged_from_previous_estimates VARCHAR(3) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE checklist DROP payments_match_cost_certificate');
        $this->addSql('ALTER TABLE checklist DROP prof_costs_reasonable_and_proportionate');
        $this->addSql('ALTER TABLE checklist DROP has_deputy_overcharged_from_previous_estimates');
    }
}

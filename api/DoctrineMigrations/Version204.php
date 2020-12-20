<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version204 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE checklist ADD deputy_charge_allowed_by_court VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec DROP registration_date');
        $this->addSql('ALTER TABLE casrec DROP last_logged_in');
        $this->addSql('ALTER TABLE casrec DROP reports_submitted');
        $this->addSql('ALTER TABLE casrec DROP last_report_submitted_at');
        $this->addSql('ALTER TABLE casrec DROP ndr_submitted_at');
        $this->addSql('ALTER TABLE casrec DROP reports_active');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE checklist DROP deputy_charge_allowed_by_court');
        $this->addSql('ALTER TABLE casrec ADD registration_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD last_logged_in TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD reports_submitted VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD last_report_submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD ndr_submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD reports_active VARCHAR(4) DEFAULT NULL');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version139 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE report_submission (id SERIAL NOT NULL, report_id INT DEFAULT NULL, archived_by INT DEFAULT NULL, created_by INT DEFAULT NULL, archived BOOLEAN DEFAULT \'false\' NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C84776C84BD2A4C0 ON report_submission (report_id)');
        $this->addSql('CREATE INDEX IDX_C84776C851B07D6D ON report_submission (archived_by)');
        $this->addSql('CREATE INDEX IDX_C84776C8DE12AB56 ON report_submission (created_by)');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C84BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C851B07D6D FOREIGN KEY (archived_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C8DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD report_submission_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7668200843 FOREIGN KEY (report_submission_id) REFERENCES report_submission (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D8698A7668200843 ON document (report_submission_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A7668200843');
        $this->addSql('DROP TABLE report_submission');
        $this->addSql('DROP INDEX IDX_D8698A7668200843');
        $this->addSql('ALTER TABLE document DROP report_submission_id');
    }
}

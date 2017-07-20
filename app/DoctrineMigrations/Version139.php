<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * report_submission
 * report_submission_documents
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

        $this->addSql('CREATE TABLE report_submission (id SERIAL NOT NULL, report_id INT DEFAULT NULL, archived_by INT DEFAULT NULL, created_by INT DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C84776C84BD2A4C0 ON report_submission (report_id)');
        $this->addSql('CREATE INDEX IDX_C84776C851B07D6D ON report_submission (archived_by)');
        $this->addSql('CREATE INDEX IDX_C84776C8DE12AB56 ON report_submission (created_by)');
        $this->addSql('CREATE TABLE report_submission_documents (document_id INT NOT NULL, submission_id INT NOT NULL, PRIMARY KEY(document_id, submission_id))');
        $this->addSql('CREATE INDEX IDX_DB573D87C33F7837 ON report_submission_documents (document_id)');
        $this->addSql('CREATE INDEX IDX_DB573D87E1FD4933 ON report_submission_documents (submission_id)');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C84BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C851B07D6D FOREIGN KEY (archived_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C8DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission_documents ADD CONSTRAINT FK_DB573D87C33F7837 FOREIGN KEY (document_id) REFERENCES report_submission (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission_documents ADD CONSTRAINT FK_DB573D87E1FD4933 FOREIGN KEY (submission_id) REFERENCES document (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report_submission_documents DROP CONSTRAINT FK_DB573D87C33F7837');
        $this->addSql('DROP TABLE report_submission');
        $this->addSql('DROP TABLE report_submission_documents');
    }
}

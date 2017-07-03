<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version137 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE document (id SERIAL NOT NULL, report_id INT DEFAULT NULL, created_by INT DEFAULT NULL, local_filename VARCHAR(150) NOT NULL, uploaded_filename VARCHAR(150) DEFAULT NULL, upload_reference VARCHAR(150) DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_document_report_id ON document (report_id)');
        $this->addSql('CREATE INDEX ix_document_created_by ON document (created_by)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76DE12AB56');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A764BD2A4C0');
        $this->addSql('DROP INDEX ix_document_report_id');
        $this->addSql('DROP INDEX ix_document_created_by');
        $this->addSql('DROP TABLE document');
    }
}

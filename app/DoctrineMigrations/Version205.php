<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version205 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE report ADD prof_dc_estimate_hc VARCHAR(10) DEFAULT NULL');
        $this->addSql('CREATE TABLE prof_deputy_estimate_cost (id SERIAL NOT NULL, report_id INT DEFAULT NULL, prof_deputy_estimate_cost_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, has_more_details BOOLEAN NOT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_40B6C2924BD2A4C0 ON prof_deputy_estimate_cost (report_id)');
        $this->addSql('ALTER TABLE prof_deputy_estimate_cost ADD CONSTRAINT FK_40B6C2924BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE report DROP prof_dc_estimate_hc');
        $this->addSql('DROP TABLE prof_deputy_estimate_cost');
    }
}

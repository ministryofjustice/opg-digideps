<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE lifestyle (id SERIAL NOT NULL, report_id INT DEFAULT NULL, care_appointments TEXT DEFAULT NULL, does_client_undertake_social_activities VARCHAR(4) DEFAULT NULL, activity_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_lifestyle_report_id ON document (report_id)');
        $this->addSql('CREATE INDEX ix_lifestyle_created_by ON document (created_by)');
        $this->addSql('ALTER TABLE lifestyle ADD CONSTRAINT FK_D8ABFC764BD2A4C00 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('DROP TABLE lifestyle');


    }
}

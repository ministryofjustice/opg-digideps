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
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D63A75CF4BD2A4C0 ON lifestyle (report_id)');
        $this->addSql('ALTER TABLE lifestyle ADD CONSTRAINT FK_D63A75CF4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ALTER wish_to_provide_documentation TYPE VARCHAR(255)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE lifestyle');
        $this->addSql('ALTER TABLE report ALTER wish_to_provide_documentation TYPE VARCHAR(3)');
        $this->addSql('ALTER TABLE note ALTER category DROP NOT NULL');
        $this->addSql('ALTER TABLE note ALTER content DROP NOT NULL');
    }
}

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

        $this->addSql('CREATE TABLE client_contact (id SERIAL NOT NULL, client_id INT DEFAULT NULL, created_by INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, job_title VARCHAR(150) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(60) DEFAULT NULL, org_name VARCHAR(150) DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_clientcontact_client_id ON client_contact (client_id)');
        $this->addSql('CREATE INDEX ix_clientcontact_created_by ON client_contact (created_by)');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT FK_1E5FA24519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT FK_1E5FA245DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE client_contact');
    }
}

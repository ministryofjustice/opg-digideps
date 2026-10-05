<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drops client contact table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE client_contact_id_seq CASCADE');
        $this->addSql('ALTER TABLE client_contact DROP CONSTRAINT fk_1e5fa24519eb6921');
        $this->addSql('ALTER TABLE client_contact DROP CONSTRAINT fk_1e5fa245de12ab56');
        $this->addSql('DROP TABLE client_contact');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE client_contact_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE client_contact (id SERIAL NOT NULL, client_id INT DEFAULT NULL, created_by INT DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, job_title VARCHAR(150) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(60) DEFAULT NULL, org_name VARCHAR(150) DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, address4 VARCHAR(200) DEFAULT NULL, address5 VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_clientcontact_client_id ON client_contact (client_id)');
        $this->addSql('CREATE INDEX ix_clientcontact_created_by ON client_contact (created_by)');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT fk_1e5fa24519eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_contact ADD CONSTRAINT fk_1e5fa245de12ab56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version080 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // ODR table
        $this->addSql('CREATE TABLE odr (id SERIAL NOT NULL, client_id INT DEFAULT NULL, submitted BOOLEAN DEFAULT NULL, submit_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_350EBBC19EB6921 ON odr (client_id)');

        //visits care
        $this->addSql('CREATE TABLE odr_visits_care (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_contact_client TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9239DE877CE4B994 ON odr_visits_care (odr_id)');


        // FKs
        $this->addSql('ALTER TABLE odr ADD CONSTRAINT FK_350EBBC19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_visits_care ADD CONSTRAINT FK_9239DE877CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE odr_visits_care DROP CONSTRAINT FK_9239DE877CE4B994');
        $this->addSql('DROP TABLE odr');
        $this->addSql('DROP TABLE odr_visits_care');
    }
}

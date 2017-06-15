<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version136 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE note (id SERIAL NOT NULL, client_id INT DEFAULT NULL, created_by INT DEFAULT NULL, last_modified_by INT DEFAULT NULL, category VARCHAR(100) DEFAULT NULL, title VARCHAR(150) NOT NULL, content TEXT DEFAULT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_modified_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_note_client_id ON note (client_id)');
        $this->addSql('CREATE INDEX ix_note_created_by ON note (created_by)');
        $this->addSql('CREATE INDEX ix_note_last_modified_by ON note (last_modified_by)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA144BD2A4C0 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');;
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1465CF370E FOREIGN KEY (last_modified_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE note');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version039 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE pdf_token_id_seq CASCADE');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE pdf_token');
        $this->addSql('ALTER TABLE report ADD report_seen BOOLEAN DEFAULT \'true\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE config (id SERIAL NOT NULL, cleanup BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE pdf_token (id SERIAL NOT NULL, report_id INT DEFAULT NULL, token VARCHAR(100) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_cea0ece14bd2a4c0 ON pdf_token (report_id)');
        $this->addSql('ALTER TABLE pdf_token ADD CONSTRAINT fk_cea0ece14bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report DROP report_seen');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version031 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE audit_log_entry (id SERIAL NOT NULL, performed_by_user_id INT DEFAULT NULL, user_edited_id INT DEFAULT NULL, performed_by_user_name VARCHAR(150) NOT NULL, performed_by_user_email VARCHAR(150) NOT NULL, ip_address VARCHAR(15) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(15) NOT NULL, user_edited_name VARCHAR(150) DEFAULT NULL, user_edited_email VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2D938A243F2ED96 ON audit_log_entry (performed_by_user_id)');
        $this->addSql('CREATE INDEX IDX_D2D938A256B7314A ON audit_log_entry (user_edited_id)');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A243F2ED96 FOREIGN KEY (performed_by_user_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A256B7314A FOREIGN KEY (user_edited_id) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE audit_log_entry');
    }
}

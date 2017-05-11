<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version132 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE fee (id SERIAL NOT NULL, report_id INT DEFAULT NULL, fee_type_id VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, has_more_details BOOLEAN NOT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_964964B54BD2A4C0 ON fee (report_id)');
        $this->addSql('ALTER TABLE fee ADD CONSTRAINT FK_964964B54BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD reason_for_no_fees TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE fee');
        $this->addSql('ALTER TABLE report DROP reason_for_no_fees');
    }
}

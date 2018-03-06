<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version169 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE prof_service_fee (id SERIAL NOT NULL, report_id INT NOT NULL, assessed_or_fixed VARCHAR(255) DEFAULT NULL, fee_type_id VARCHAR(255) NOT NULL, other_fee_details VARCHAR(255) DEFAULT NULL, service_type_id VARCHAR(255) NOT NULL, amount_charged NUMERIC(14, 2) DEFAULT NULL, payment_received VARCHAR(255) DEFAULT NULL, amount_received NUMERIC(14, 2) DEFAULT NULL, payment_received_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7829CE7E4BD2A4C0 ON prof_service_fee (report_id)');
        $this->addSql('ALTER TABLE prof_service_fee ADD CONSTRAINT FK_7829CE7E4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD current_prof_payments_received VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD previous_prof_fees_estimate_given VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD prof_fees_estimate_scco_reason TEXT DEFAULT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE prof_service_fee');
        $this->addSql('ALTER TABLE report DROP current_prof_payment_received');
        $this->addSql('ALTER TABLE report DROP prof_fees_estimate_scco_reason');

    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1494';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE prof_service_fee_id_seq CASCADE');
        $this->addSql('ALTER TABLE prof_service_fee DROP CONSTRAINT fk_7829ce7e4bd2a4c0');
        $this->addSql('DROP TABLE prof_service_fee');
        $this->addSql('ALTER TABLE client_benefits_check ALTER when_last_checked_entitlement DROP NOT NULL');
        $this->addSql('ALTER TABLE dd_user DROP ad_managed');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE prof_service_fee_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE prof_service_fee (id SERIAL NOT NULL, report_id INT NOT NULL, assessed_or_fixed VARCHAR(255) DEFAULT NULL, other_fee_details VARCHAR(255) DEFAULT NULL, service_type_id VARCHAR(255) NOT NULL, amount_charged NUMERIC(14, 2) DEFAULT NULL, payment_received VARCHAR(255) DEFAULT NULL, amount_received NUMERIC(14, 2) DEFAULT NULL, payment_received_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, fee_type_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_7829ce7e4bd2a4c0 ON prof_service_fee (report_id)');
        $this->addSql('ALTER TABLE prof_service_fee ADD CONSTRAINT fk_7829ce7e4bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_benefits_check ALTER when_last_checked_entitlement SET NOT NULL');
        $this->addSql('ALTER TABLE dd_user ADD ad_managed BOOLEAN DEFAULT false');
    }
}

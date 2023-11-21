<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add migrations for ClientBenefitsCheck';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_benefits_check (id UUID NOT NULL, report_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, when_last_checked_entitlement VARCHAR(255) NOT NULL, do_others_receive_income_on_clients_behalf VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B0206BD54BD2A4C0 ON client_benefits_check (report_id)');
        $this->addSql('COMMENT ON COLUMN client_benefits_check.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE income_received_on_clients_behalf (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, income_type VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, clientBenefitsCheck_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F551CA35606E920 ON income_received_on_clients_behalf (clientBenefitsCheck_id)');
        $this->addSql('COMMENT ON COLUMN income_received_on_clients_behalf.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN income_received_on_clients_behalf.clientBenefitsCheck_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE client_benefits_check ADD CONSTRAINT FK_B0206BD54BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD CONSTRAINT FK_2F551CA35606E920 FOREIGN KEY (clientBenefitsCheck_id) REFERENCES client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP CONSTRAINT FK_2F551CA35606E920');
        $this->addSql('DROP TABLE client_benefits_check');
        $this->addSql('DROP TABLE income_received_on_clients_behalf');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version260 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add NDR versions of ClientBenefitsCheck and IncomeReceivedOnClientsBehalf';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE odr_client_benefits_check (id UUID NOT NULL, ndr_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, when_last_checked_entitlement VARCHAR(255) NOT NULL, date_last_checked_entitlement TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, never_checked_explanation TEXT DEFAULT NULL, do_others_receive_income_on_clients_behalf VARCHAR(255) DEFAULT NULL, dont_know_income_explanation TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1457B1B0B7B86A31 ON odr_client_benefits_check (ndr_id)');
        $this->addSql('COMMENT ON COLUMN odr_client_benefits_check.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE odr_income_received_on_clients_behalf (id UUID NOT NULL, client_benefits_check_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, income_type VARCHAR(255) NOT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DF89FA855064A0FF ON odr_income_received_on_clients_behalf (client_benefits_check_id)');
        $this->addSql('COMMENT ON COLUMN odr_income_received_on_clients_behalf.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN odr_income_received_on_clients_behalf.client_benefits_check_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE odr_client_benefits_check ADD CONSTRAINT FK_1457B1B0B7B86A31 FOREIGN KEY (ndr_id) REFERENCES odr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ADD CONSTRAINT FK_DF89FA855064A0FF FOREIGN KEY (client_benefits_check_id) REFERENCES odr_client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf DROP CONSTRAINT FK_DF89FA855064A0FF');
        $this->addSql('DROP TABLE odr_client_benefits_check');
        $this->addSql('DROP TABLE odr_income_received_on_clients_behalf');
    }
}

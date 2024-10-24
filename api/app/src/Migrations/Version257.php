<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Switch to one-to-one relationship for ClientBenefitCheck -> Report, add underscores to foreign key column on income_received_on_clients_behalf, break date checked question into two columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check ADD date_last_checked_entitlement TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('DROP INDEX idx_b0206bd54bd2a4c0');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B0206BD54BD2A4C0 ON client_benefits_check (report_id)');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP CONSTRAINT fk_2f551ca35606e920');
        $this->addSql('DROP INDEX uniq_2f551ca35606e920');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf RENAME COLUMN clientbenefitscheck_id TO client_benefits_check_id');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD CONSTRAINT FK_2F551CA35064A0FF FOREIGN KEY (client_benefits_check_id) REFERENCES client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2F551CA35064A0FF ON income_received_on_clients_behalf (client_benefits_check_id)');
        $this->addSql('ALTER TABLE client_benefits_check ADD never_checked_explanation TEXT');
        $this->addSql('ALTER TABLE client_benefits_check ALTER do_others_receive_income_on_clients_behalf DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check DROP date_last_checked_entitlement');
        $this->addSql('DROP INDEX UNIQ_B0206BD54BD2A4C0');
        $this->addSql('CREATE INDEX idx_b0206bd54bd2a4c0 ON client_benefits_check (report_id)');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP CONSTRAINT FK_2F551CA35064A0FF');
        $this->addSql('DROP INDEX UNIQ_2F551CA35064A0FF');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf RENAME COLUMN client_benefits_check_id TO clientbenefitscheck_id');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD CONSTRAINT fk_2f551ca35606e920 FOREIGN KEY (clientbenefitscheck_id) REFERENCES client_benefits_check (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_2f551ca35606e920 ON income_received_on_clients_behalf (clientbenefitscheck_id)');
        $this->addSql('ALTER TABLE client_benefits_check DROP never_checked_explanation');
        $this->addSql('ALTER TABLE client_benefits_check ALTER do_others_receive_income_on_clients_behalf SET NOT NULL');
    }
}

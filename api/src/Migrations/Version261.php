<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version261 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename columns to use money instead of income and add who received money column for income benefits check';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check RENAME COLUMN do_others_receive_income_on_clients_behalf TO do_others_receive_money_on_clients_behalf');
        $this->addSql('ALTER TABLE client_benefits_check RENAME COLUMN dont_know_income_explanation TO dont_know_money_explanation');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD who_received_money VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf RENAME COLUMN income_type TO money_type');

        $this->addSql('ALTER TABLE odr_client_benefits_check RENAME COLUMN do_others_receive_income_on_clients_behalf TO do_others_receive_money_on_clients_behalf');
        $this->addSql('ALTER TABLE odr_client_benefits_check RENAME COLUMN dont_know_income_explanation TO dont_know_money_explanation');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ADD who_received_money VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf RENAME COLUMN income_type TO money_type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check RENAME COLUMN do_others_receive_money_on_clients_behalf TO do_others_receive_income_on_clients_behalf');
        $this->addSql('ALTER TABLE client_benefits_check RENAME COLUMN dont_know_money_explanation TO dont_know_income_explanation');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ADD income_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP money_type');
        $this->addSql('ALTER TABLE income_received_on_clients_behalf DROP who_received_money');

        $this->addSql('ALTER TABLE odr_client_benefits_check RENAME COLUMN do_others_receive_money_on_clients_behalf TO do_others_receive_income_on_clients_behalf');
        $this->addSql('ALTER TABLE odr_client_benefits_check RENAME COLUMN dont_know_money_explanation TO dont_know_income_explanation');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ADD income_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf DROP money_type');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf DROP who_received_money');
    }
}

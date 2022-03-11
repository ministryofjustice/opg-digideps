<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version262 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove not null constraint from who_received_money to allow for migrations on existing DBs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ALTER who_received_money DROP NOT NULL');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ALTER who_received_money DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE income_received_on_clients_behalf ALTER who_received_money SET NOT NULL');
        $this->addSql('ALTER TABLE odr_income_received_on_clients_behalf ALTER who_received_money SET NOT NULL');
    }
}

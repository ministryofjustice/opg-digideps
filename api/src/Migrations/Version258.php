<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version258 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add explanation column for clients benefits income question and fix typo in income_received_on_clients_behalf to change fron OneToOne to ManyToOne';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check ADD dont_know_income_explanation TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_2f551ca35064a0ff');
        $this->addSql('CREATE INDEX IDX_2F551CA35064A0FF ON income_received_on_clients_behalf (client_benefits_check_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_benefits_check DROP dont_know_income_explanation');
        $this->addSql('DROP INDEX IDX_2F551CA35064A0FF');
        $this->addSql('CREATE UNIQUE INDEX uniq_2f551ca35064a0ff ON income_received_on_clients_behalf (client_benefits_check_id)');
    }
}

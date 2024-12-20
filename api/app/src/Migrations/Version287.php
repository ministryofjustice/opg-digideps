<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


class Version287 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds columns to store the new client data from the daily import';
    }
    
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE client_deputy (client_id INT NOT NULL, deputy_id INT NOT NULL, PRIMARY KEY(client_id, deputy_id))');
        $this->addSql('CREATE INDEX cd_client_idx ON client_deputy (client_id)');
        $this->addSql('CREATE INDEX cd_deputy_idx ON client_deputy (deputy_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX cd_client_idx');
        $this->addSql('DROP INDEX cd_deputy_idx');
        $this->addSql('DROP TABLE client_deputy');
    }
}

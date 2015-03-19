<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version017 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE account_transaction (id INT NOT NULL, account_id INT DEFAULT NULL, account_transaction_type_id VARCHAR(255) DEFAULT NULL, amount NUMERIC(14, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A370F9D29B6B5FBA ON account_transaction (account_id)');
        $this->addSql('CREATE INDEX IDX_A370F9D2387F8B02 ON account_transaction (account_transaction_type_id)');
        $this->addSql('CREATE TABLE account_transaction_type (id VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE account_transaction ADD CONSTRAINT FK_A370F9D29B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE account_transaction ADD CONSTRAINT FK_A370F9D2387F8B02 FOREIGN KEY (account_transaction_type_id) REFERENCES account_transaction_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account_transaction DROP CONSTRAINT FK_A370F9D2387F8B02');
        $this->addSql('DROP TABLE account_transaction');
        $this->addSql('DROP TABLE account_transaction_type');
    }
}

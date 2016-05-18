<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version057 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE account_transaction_type, account_transaction');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE account_transaction (id SERIAL NOT NULL, account_id INT DEFAULT NULL, account_transaction_type_id VARCHAR(255) DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, more_details TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE account_transaction_type (id VARCHAR(255) NOT NULL, has_more_details BOOLEAN NOT NULL, display_order INT DEFAULT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }
}

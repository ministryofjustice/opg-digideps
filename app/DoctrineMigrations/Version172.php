<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version172 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE gift ADD bank_account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gift ADD CONSTRAINT FK_A47C990D12CB990C FOREIGN KEY (bank_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A47C990D12CB990C ON gift (bank_account_id)');
        $this->addSql('ALTER TABLE money_transaction ADD bank_account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE money_transaction ADD CONSTRAINT FK_D21254E212CB990C FOREIGN KEY (bank_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D21254E212CB990C ON money_transaction (bank_account_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE money_transaction DROP CONSTRAINT FK_D21254E212CB990C');
        $this->addSql('DROP INDEX IDX_D21254E212CB990C');
        $this->addSql('ALTER TABLE money_transaction DROP bank_account_id');
        $this->addSql('ALTER TABLE gift DROP CONSTRAINT FK_A47C990D12CB990C');
        $this->addSql('DROP INDEX IDX_A47C990D12CB990C');
        $this->addSql('ALTER TABLE gift DROP bank_account_id');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version060 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE money_transfer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE money_transfer (id INT NOT NULL, from_account_id INT DEFAULT NULL, to_account_id INT DEFAULT NULL, report_id INT DEFAULT NULL, amount NUMERIC(14, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A15E50EEB0CF99BD ON money_transfer (from_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EEBC58BDC7 ON money_transfer (to_account_id)');
        $this->addSql('CREATE INDEX IDX_A15E50EE4BD2A4C0 ON money_transfer (report_id)');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEB0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EEBC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE money_transfer ADD CONSTRAINT FK_A15E50EE4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE money_transfer_id_seq CASCADE');
        $this->addSql('DROP TABLE money_transfer');
    }
}

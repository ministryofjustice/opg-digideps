<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version220 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Replaced client.named deputy with new foreign key to dedicated Named Deputy table to store named deputy details';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // reset all references to named deputy to be null to be reassociated with new ids
        $this->addSql('UPDATE client SET named_deputy = NULL');

        $this->addSql('CREATE TABLE named_deputy (id SERIAL NOT NULL, deputy_no VARCHAR(15) NOT NULL, deputy_type VARCHAR(5) DEFAULT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, email1 VARCHAR(60) NOT NULL, email2 VARCHAR(60) DEFAULT NULL, email3 VARCHAR(60) DEFAULT NULL, dep_addr_no INT DEFAULT NULL, address1 VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, address3 VARCHAR(200) DEFAULT NULL, address4 VARCHAR(200) DEFAULT NULL, address5 VARCHAR(200) DEFAULT NULL, address_postcode VARCHAR(10) DEFAULT NULL, address_country VARCHAR(10) DEFAULT NULL, phone_main VARCHAR(20) DEFAULT NULL, phone_alternative VARCHAR(20) DEFAULT NULL, fee_payer BOOLEAN DEFAULT NULL, corres BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX named_deputy_no_idx ON named_deputy (deputy_no)');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c74404551058689');
        $this->addSql('DROP INDEX idx_c74404551058689');
        $this->addSql('ALTER TABLE client RENAME COLUMN named_deputy TO named_deputy_id');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045595162E7C FOREIGN KEY (named_deputy_id) REFERENCES named_deputy (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C744045595162E7C ON client (named_deputy_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C744045595162E7C');
        $this->addSql('DROP TABLE named_deputy');
        $this->addSql('DROP INDEX IDX_C744045595162E7C');
        $this->addSql('ALTER TABLE client RENAME COLUMN named_deputy_id TO named_deputy');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c74404551058689 FOREIGN KEY (named_deputy) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c74404551058689 ON client (named_deputy)');
    }
}

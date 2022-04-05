<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version263 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Delete content of named_deputy table, remove deputy_type and deputy_addr_no, change deputy_no to deputy_uid.
Changes to support migratrion from Casrec to Sirius';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM named_deputy');
        $this->addSql('DROP INDEX uniq_105868993fcdcf4');
        $this->addSql('DROP INDEX named_deputy_no_idx');
        $this->addSql('ALTER TABLE named_deputy DROP deputy_type');
        $this->addSql('ALTER TABLE named_deputy DROP dep_addr_no');
        $this->addSql('ALTER TABLE named_deputy DROP deputy_no');
        $this->addSql('ALTER TABLE named_deputy DROP fee_payer');
        $this->addSql('ALTER TABLE named_deputy DROP corres');
        $this->addSql('ALTER TABLE named_deputy ADD deputy_uid VARCHAR(20)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1058689D625B8D2 ON named_deputy (deputy_uid)');
        $this->addSql('CREATE INDEX named_deputy_uid_idx ON named_deputy (deputy_uid)');

        $this->addSql('ALTER TABLE client RENAME county to address3');
        $this->addSql('ALTER TABLE client ALTER COLUMN address3 TYPE VARCHAR(200)');
        $this->addSql('ALTER TABLE client ADD address4 VARCHAR(200)');
        $this->addSql('ALTER TABLE client ADD address5 VARCHAR(200)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM named_deputy');
        $this->addSql('DROP INDEX UNIQ_1058689D625B8D2');
        $this->addSql('DROP INDEX named_deputy_uid_idx');
        $this->addSql('ALTER TABLE named_deputy DROP deputy_uid');
        $this->addSql('ALTER TABLE named_deputy ADD deputy_type VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD dep_addr_no INT DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD deputy_no VARCHAR(20)');
        $this->addSql('ALTER TABLE named_deputy ADD fee_payer BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE named_deputy ADD corres BOOLEAN DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_105868993fcdcf4 ON named_deputy (deputy_no)');
        $this->addSql('CREATE INDEX named_deputy_no_idx ON named_deputy (deputy_no)');

        $this->addSql('ALTER TABLE client RENAME address3 to county');
        $this->addSql('ALTER TABLE client ALTER COLUMN county TYPE VARCHAR(75)');
        $this->addSql('ALTER TABLE client DROP address4');
        $this->addSql('ALTER TABLE client DROP address5');
    }
}

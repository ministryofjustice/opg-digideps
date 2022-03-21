<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version262 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Empty data from casrec and add/update column names for new Sirius CSV content';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM casrec');
        $this->addSql('ALTER TABLE casrec ADD deputy_address_1 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD deputy_address_2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD deputy_address_3 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD deputy_address_4 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD deputy_address_5 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD ndr BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD order_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD is_co_deputy BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec DROP corref');
        $this->addSql('ALTER TABLE casrec DROP other_columns');
        $this->addSql('ALTER TABLE casrec DROP source');
        $this->addSql('ALTER TABLE casrec RENAME COLUMN deputy_no TO deputy_uid');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE casrec ADD corref VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD other_columns TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE casrec ADD source VARCHAR(255) DEFAULT \'casrec\'');
        $this->addSql('ALTER TABLE casrec DROP deputy_address_1');
        $this->addSql('ALTER TABLE casrec DROP deputy_address_2');
        $this->addSql('ALTER TABLE casrec DROP deputy_address_3');
        $this->addSql('ALTER TABLE casrec DROP deputy_address_4');
        $this->addSql('ALTER TABLE casrec DROP deputy_address_5');
        $this->addSql('ALTER TABLE casrec DROP ndr');
        $this->addSql('ALTER TABLE casrec DROP order_type');
        $this->addSql('ALTER TABLE casrec DROP is_co_deputy');
        $this->addSql('ALTER TABLE casrec RENAME COLUMN deputy_uid TO deputy_no');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version014 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE decision ADD title VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE decision ADD description TEXT NOT NULL');
        $this->addSql('ALTER TABLE decision ADD client_involved_boolean BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE decision ADD client_involved_details TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE decision DROP decisions');
        $this->addSql('ALTER TABLE decision DROP explanation');
        $this->addSql('ALTER TABLE decision DROP last_edit');
        $this->addSql('ALTER TABLE decision RENAME COLUMN d_date TO decision_date');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE decision ADD explanation TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE decision ADD last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE decision DROP title');
        $this->addSql('ALTER TABLE decision DROP description');
        $this->addSql('ALTER TABLE decision DROP client_involved_boolean');
        $this->addSql('ALTER TABLE decision RENAME COLUMN client_involved_details TO decisions');
        $this->addSql('ALTER TABLE decision RENAME COLUMN decision_date TO d_date');
    }
}

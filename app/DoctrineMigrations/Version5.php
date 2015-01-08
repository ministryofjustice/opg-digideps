<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version5 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('ALTER TABLE cases RENAME TO client');
        $this->addSql('ALTER TABLE cases_id_seq RENAME TO client_id_seq');
        $this->addSql('ALTER TABLE deputy_case RENAME COLUMN cases_id TO client_id');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        
        $this->addSql('ALTER TABLE client RENAME TO cases');
        $this->addSql('ALTER TABLE client_id_seq RENAME TO cases_id_seq');
        $this->addSql('ALTER TABLE deputy_case RENAME COLUMN client_id TO cases_id');
    }
}

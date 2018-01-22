<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version162 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // rename ndr to ndr

        #$this->addSql(" ALTER TABLE dd_user RENAME ndr_enabled TO ndr_enabled; ");
        #$this->addSql(" ALTER TABLE ndr RENAME TO ndr; ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        #$this->addSql(" ALTER TABLE dd_user RENAME ndr_enabled TO ndr_enabled; ");
        #$this->addSql(" ALTER TABLE ndr RENAME TO ndr; ");
    }
}

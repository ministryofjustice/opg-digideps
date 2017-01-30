<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version110 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP CONSTRAINT fk_c42f7784a47aeb9');
        $this->addSql('DROP SEQUENCE court_order_type_id_seq CASCADE');
        $this->addSql('DROP TABLE court_order_type');
        $this->addSql('ALTER TABLE client DROP allowed_court_order_types');
        $this->addSql('DROP INDEX idx_c42f7784a47aeb9');
        $this->addSql('ALTER TABLE report DROP court_order_type_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // use snapshot to restore this
    }
}

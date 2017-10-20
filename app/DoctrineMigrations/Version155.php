<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version155 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE account ALTER is_closed SET DEFAULT \'false\'');
        $this->addSql('ALTER TABLE account ALTER is_closed DROP NOT NULL');
        $this->addSql('UPDATE dd_user SET codeputy_client_confirmed = FALSE WHERE codeputy_client_confirmed IS NULL');
        $this->addSql('ALTER TABLE dd_user ALTER codeputy_client_confirmed SET NOT NULL');
        $this->addSql('ALTER TABLE note ALTER category DROP NOT NULL');
        $this->addSql('ALTER TABLE note ALTER content DROP NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account ALTER is_closed DROP DEFAULT');
        $this->addSql('ALTER TABLE account ALTER is_closed SET NOT NULL');
        $this->addSql('ALTER TABLE note ALTER category SET NOT NULL');
        $this->addSql('ALTER TABLE note ALTER content SET NOT NULL');
        $this->addSql('ALTER TABLE dd_user ALTER codeputy_client_confirmed DROP NOT NULL');
    }
}

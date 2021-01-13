<?php

namespace Application\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version192 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("UPDATE report set type = type || '-5' WHERE id IN
          (
              SELECT id FROM report WHERE client_id IN
              (
                select client_id from deputy_case WHERE user_id IN
                (
                    select id from dd_user where role_name in ('ROLE_PROF_ADMIN','ROLE_PROF_NAMED','ROLE_PROF_TEAM_MEMBER')
                )
              ) AND type not LIKE '%-5'
          )");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}

<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version14 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql("UPDATE role set role = 'ROLE_ADMIN' where id = 1 ");
        $this->addSql("UPDATE role set role = 'ROLE_LAY_DEPUTY' where id = 2 ");
        $this->addSql("UPDATE role set role = 'ROLE_PROFESSIONAL_DEPUTY' where id = 3 ");
        $this->addSql("UPDATE role set role = 'ROLE_LOCAL_AUTHORITY_DEPUTY' where id = 4");
        $this->addSql("DELETE FROM role WHERE name = 'Visitor' ");

    }

    public function down(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('UPDATE role set role = null WHERE id in (1,2,3,4)');
        $this->addSql("INSERT into role VALUES (5,'Visitor', null)");

    }
}

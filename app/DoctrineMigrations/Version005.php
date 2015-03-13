<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * delete profile table
 */
class Version005 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE profile_id_seq CASCADE');
        $this->addSql('DROP TABLE profile');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE profile (id SERIAL NOT NULL, user_id INT DEFAULT NULL, title_id INT DEFAULT NULL, phone_home VARCHAR(20) DEFAULT NULL, phone_mobile VARCHAR(20) DEFAULT NULL, address VARCHAR(200) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, company VARCHAR(100) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, phone_work VARCHAR(20) DEFAULT NULL, trustcorp VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT fk_8157aa0fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT fk_8157aa0fa9f87bd FOREIGN KEY (title_id) REFERENCES title (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

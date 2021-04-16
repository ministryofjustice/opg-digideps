<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version250 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Removing teams tables and clearing associations between team members and clients';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("DELETE from deputy_case WHERE user_id IN (SELECT id FROM dd_user WHERE role_name != 'ROLE_LAY_DEPUTY')");
        $this->addSql('ALTER TABLE user_team DROP CONSTRAINT fk_be61ead6296cd8ae');
        $this->addSql('DROP SEQUENCE dd_team_id_seq CASCADE');
        $this->addSql('DROP TABLE dd_team');
        $this->addSql('DROP TABLE user_team');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('CREATE SEQUENCE dd_team_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE dd_team (id SERIAL NOT NULL, team_name VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_team (user_id INT NOT NULL, team_id INT NOT NULL, PRIMARY KEY(user_id, team_id))');
        $this->addSql('CREATE INDEX idx_be61ead6296cd8ae ON user_team (team_id)');
        $this->addSql('CREATE INDEX idx_be61ead6a76ed395 ON user_team (user_id)');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT fk_be61ead6a76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_team ADD CONSTRAINT fk_be61ead6296cd8ae FOREIGN KEY (team_id) REFERENCES dd_team (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

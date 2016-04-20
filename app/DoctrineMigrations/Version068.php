<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version068 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        // asset type, remove default value
        $this->addSql('ALTER TABLE asset ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE asset ALTER occupants TYPE VARCHAR(550)');
        // clean safeguarding
        $this->addSql('ALTER TABLE safeguarding DROP IF EXISTS how_often_do_you_visit');
        $this->addSql('ALTER TABLE safeguarding DROP IF EXISTS how_often_do_you_phone_or_video_call');
        $this->addSql('ALTER TABLE safeguarding DROP IF EXISTS how_often_do_you_write_email_or_letter');
        $this->addSql('ALTER TABLE safeguarding DROP IF EXISTS how_often_does_client_see_other_people');
        $this->addSql('ALTER TABLE safeguarding DROP IF EXISTS anything_else_to_tell');
        
        // clean transaction
        $this->addSql('ALTER TABLE transaction DROP amount');
        $this->addSql('ALTER TABLE transaction ALTER amounts TYPE TEXT');
        $this->addSql('ALTER TABLE transaction ALTER amounts DROP DEFAULT');
        
        // old seqs
        $this->addSql('DROP SEQUENCE IF EXISTS access_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS  auth_code_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS  oauth2_client_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS  refresh_token_id_seq CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE asset ALTER type SET DEFAULT \'other\'');
        $this->addSql('ALTER TABLE asset ALTER occupants TYPE VARCHAR(650)');
        $this->addSql('ALTER TABLE safeguarding ADD how_often_do_you_visit VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE safeguarding ADD how_often_do_you_phone_or_video_call VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE safeguarding ADD how_often_do_you_write_email_or_letter VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE safeguarding ADD how_often_does_client_see_other_people VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE safeguarding ADD anything_else_to_tell TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD amount NUMERIC(14, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ALTER amounts TYPE TEXT');
        $this->addSql('ALTER TABLE transaction ALTER amounts DROP DEFAULT');
    }
}

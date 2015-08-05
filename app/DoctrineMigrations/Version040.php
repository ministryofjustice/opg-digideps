<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version040 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ADD do_you_live_with_client VARCHAR(4) NOT NULL');
        $this->addSql('ALTER TABLE report ADD how_often_do_you_visit VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD how_often_do_you_phone_or_video_call VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD how_often_do_you_write_email_or_letter VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD how_often_does_client_see_other_people VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD anything_else_to_tell TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD does_client_receive_paid_care TEXT NOT NULL');
        $this->addSql('ALTER TABLE report ADD who_is_doing_the_caring TEXT NOT NULL');
        $this->addSql('ALTER TABLE report ADD does_client_have_a_care_plan VARCHAR(4) NOT NULL');
        $this->addSql('ALTER TABLE report ADD when_was_care_plan_last_reviewed TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP do_you_live_with_client');
        $this->addSql('ALTER TABLE report DROP how_often_do_you_visit');
        $this->addSql('ALTER TABLE report DROP how_often_do_you_phone_or_video_call');
        $this->addSql('ALTER TABLE report DROP how_often_do_you_write_email_or_letter');
        $this->addSql('ALTER TABLE report DROP how_often_does_client_see_other_people');
        $this->addSql('ALTER TABLE report DROP anything_else_to_tell');
        $this->addSql('ALTER TABLE report DROP does_client_receive_paid_care');
        $this->addSql('ALTER TABLE report DROP who_is_doing_the_caring');
        $this->addSql('ALTER TABLE report DROP does_client_have_a_care_plan');
        $this->addSql('ALTER TABLE report DROP when_was_care_plan_last_reviewed');
    }
}

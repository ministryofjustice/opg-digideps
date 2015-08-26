<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version041 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE safeguarding (id SERIAL NOT NULL, report_id INT DEFAULT NULL, do_you_live_with_client VARCHAR(4) DEFAULT NULL, how_often_do_you_visit VARCHAR(55) DEFAULT NULL, how_often_do_you_phone_or_video_call VARCHAR(55) DEFAULT NULL, how_often_do_you_write_email_or_letter VARCHAR(55) DEFAULT NULL, how_often_does_client_see_other_people VARCHAR(55) DEFAULT NULL, anything_else_to_tell TEXT DEFAULT NULL, does_client_receive_paid_care TEXT DEFAULT NULL, how_is_care_funded VARCHAR(255) DEFAULT NULL, who_is_doing_the_caring TEXT DEFAULT NULL, does_client_have_a_care_plan VARCHAR(4) DEFAULT NULL, when_was_care_plan_last_reviewed DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C7877184BD2A4C0 ON safeguarding (report_id)');
        $this->addSql('ALTER TABLE safeguarding ADD CONSTRAINT FK_8C7877184BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD safeguarding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77844AD4214B FOREIGN KEY (safeguarding_id) REFERENCES safeguarding (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F77844AD4214B ON report (safeguarding_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F77844AD4214B');
        $this->addSql('DROP TABLE safeguarding');
        $this->addSql('DROP INDEX UNIQ_C42F77844AD4214B');
        $this->addSql('ALTER TABLE report DROP safeguarding_id');
    }
}

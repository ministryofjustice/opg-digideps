<?php
namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version041 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ALTER do_you_live_with_client DROP NOT NULL');
        $this->addSql('ALTER TABLE report ALTER does_client_receive_paid_care DROP NOT NULL');
        $this->addSql('ALTER TABLE report ALTER who_is_doing_the_caring DROP NOT NULL');
        $this->addSql('ALTER TABLE report ALTER does_client_have_a_care_plan DROP NOT NULL');
        $this->addSql('ALTER TABLE report ALTER when_was_care_plan_last_reviewed DROP NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ALTER do_you_live_with_client SET NOT NULL');
        $this->addSql('ALTER TABLE report ALTER does_client_receive_paid_care SET NOT NULL');
        $this->addSql('ALTER TABLE report ALTER who_is_doing_the_caring SET NOT NULL');
        $this->addSql('ALTER TABLE report ALTER does_client_have_a_care_plan SET NOT NULL');
        $this->addSql('ALTER TABLE report ALTER when_was_care_plan_last_reviewed SET NOT NULL');
    }
}

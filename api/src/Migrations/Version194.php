<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version194 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE INDEX rs_created_on_idx ON report_submission (created_on)');
        $this->addSql('CREATE INDEX created_on_idx ON report_submission (created_on)');
        $this->addSql('CREATE INDEX submit_date_idx ON report (submit_date)');
        $this->addSql('CREATE INDEX odr_submitted_idx ON odr (submitted)');
        $this->addSql('CREATE INDEX odr_submit_date_idx ON odr (submit_date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX rs_created_on_idx');
        $this->addSql('DROP INDEX created_on_idx');
        $this->addSql('DROP INDEX odr_submitted_idx');
        $this->addSql('DROP INDEX odr_submit_date_idx');
        $this->addSql('DROP INDEX submit_date_idx');
    }
}

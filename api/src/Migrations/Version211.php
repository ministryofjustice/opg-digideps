<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version211 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ADD prof_dc_how_charged VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE report DROP prof_dc_hc_fixed');
        $this->addSql('ALTER TABLE report DROP prof_dc_hc_assessed');
        $this->addSql('ALTER TABLE report ALTER prof_dc_estimate_management_cost TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE report ALTER prof_dc_estimate_management_cost DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report ADD prof_dc_hc_fixed BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD prof_dc_hc_assessed BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE report DROP prof_dc_how_charged');
        $this->addSql('ALTER TABLE report ALTER prof_dc_estimate_management_cost TYPE NUMERIC(14, 2)');
        $this->addSql('ALTER TABLE report ALTER prof_dc_estimate_management_cost DROP DEFAULT');
    }
}

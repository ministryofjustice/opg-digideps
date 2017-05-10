<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * delete cascade for default children records added on report creation
 *
 */
class Version128 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE money_short_category DROP CONSTRAINT FK_106370F74BD2A4C0');
        $this->addSql('ALTER TABLE money_short_category ADD CONSTRAINT FK_106370F74BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE debt DROP CONSTRAINT FK_DBBF0A834BD2A4C0');
        $this->addSql('ALTER TABLE debt ADD CONSTRAINT FK_DBBF0A834BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');


    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE money_short_category DROP CONSTRAINT fk_106370f74bd2a4c0');
        $this->addSql('ALTER TABLE money_short_category ADD CONSTRAINT fk_106370f74bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE debt DROP CONSTRAINT fk_dbbf0a834bd2a4c0');
        $this->addSql('ALTER TABLE debt ADD CONSTRAINT fk_dbbf0a834bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

    }
}

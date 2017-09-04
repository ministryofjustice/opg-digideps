<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version144 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE report_submission DROP CONSTRAINT FK_C84776C8DE12AB56');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT FK_C84776C8DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT FK_D8698A76DE12AB56');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA14DE12AB56');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT fk_cfbdfa14de12ab56');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT fk_cfbdfa14de12ab56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_submission DROP CONSTRAINT fk_c84776c8de12ab56');
        $this->addSql('ALTER TABLE report_submission ADD CONSTRAINT fk_c84776c8de12ab56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE document DROP CONSTRAINT fk_d8698a76de12ab56');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_d8698a76de12ab56 FOREIGN KEY (created_by) REFERENCES dd_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

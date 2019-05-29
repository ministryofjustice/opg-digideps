<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version182 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE checklist (id SERIAL NOT NULL, report_id INT NOT NULL, submitted_by INT DEFAULT NULL, reporting_period_accurate VARCHAR(3) DEFAULT NULL, contact_details_upto_date VARCHAR(3) DEFAULT NULL, deputy_full_name_accurate_in_casrec VARCHAR(3) DEFAULT NULL, decisions_satisfactory VARCHAR(3) DEFAULT NULL, consultations_satisfactory VARCHAR(3) DEFAULT NULL, care_arrangements VARCHAR(3) DEFAULT NULL, assets_declared_and_managed VARCHAR(3) DEFAULT NULL, debts_managed VARCHAR(3) DEFAULT NULL, open_closing_balances_match VARCHAR(3) DEFAULT NULL, accounts_balance VARCHAR(3) DEFAULT NULL, money_movements_acceptable VARCHAR(3) DEFAULT NULL, bond_adequate VARCHAR(3) DEFAULT NULL, bond_order_match_casrec VARCHAR(3) DEFAULT NULL, future_significant_financial_decisions VARCHAR(3) DEFAULT NULL, has_deputy_raised_concerns VARCHAR(3) DEFAULT NULL, case_worker_satisified VARCHAR(3) DEFAULT NULL, lodging_summary TEXT DEFAULT NULL, final_decision VARCHAR(30) DEFAULT NULL, submitted_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C696D2F4BD2A4C0 ON checklist (report_id)');
        $this->addSql('CREATE INDEX IDX_5C696D2F641EE842 ON checklist (submitted_by)');
        $this->addSql('CREATE TABLE checklist_information (id SERIAL NOT NULL, checklist_id INT NOT NULL, created_by INT DEFAULT NULL, information TEXT NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_checklist_information_checklist_id ON checklist_information (checklist_id)');
        $this->addSql('CREATE INDEX ix_checklist_information_created_by ON checklist_information (created_by)');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F641EE842 FOREIGN KEY (submitted_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist_information ADD CONSTRAINT FK_3FB5A813B16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist_information ADD CONSTRAINT FK_3FB5A813DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
        $this->addSql('ALTER TABLE checklist_information DROP CONSTRAINT FK_3FB5A813B16D08A7');
        $this->addSql('DROP TABLE checklist');
        $this->addSql('DROP TABLE checklist_information');
    }
}

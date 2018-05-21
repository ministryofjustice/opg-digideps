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

        $this->addSql('CREATE TABLE checklist (id SERIAL NOT NULL, report_id INT NOT NULL, reportingPeriodAccurate VARCHAR(3) DEFAULT NULL, contactDetailsUptoDate VARCHAR(3) DEFAULT NULL, deputyFullNameAccurateinCasrec VARCHAR(3) DEFAULT NULL, decisionsSatisfactory VARCHAR(3) DEFAULT NULL, consultationsSatisfactory VARCHAR(3) DEFAULT NULL, careArrangements VARCHAR(3) DEFAULT NULL, assetsDeclaredAndManaged VARCHAR(3) DEFAULT NULL, debtsManaged VARCHAR(3) DEFAULT NULL, openClosingBalancesMatch VARCHAR(3) DEFAULT NULL, accountsBalance VARCHAR(3) DEFAULT NULL, moneyMovementsAcceptable VARCHAR(3) DEFAULT NULL, bondAdequate VARCHAR(3) DEFAULT NULL, bondOrderMatchCasrec VARCHAR(3) DEFAULT NULL, futureSignificantFinancialDecisions VARCHAR(3) DEFAULT NULL, hasDeputyRaisedConcerns VARCHAR(3) DEFAULT NULL, caseWorkerSatisified VARCHAR(3) DEFAULT NULL, decision TEXT DEFAULT NULL, caseManagerName VARCHAR(255) DEFAULT NULL, lodgingSummary TEXT DEFAULT NULL, finalDecision VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C696D2F4BD2A4C0 ON checklist (report_id)');
        $this->addSql('CREATE TABLE checklist_information (id SERIAL NOT NULL, checklist_id INT NOT NULL, created_by INT DEFAULT NULL, information TEXT NOT NULL, created_on TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX ix_checklist_information_checklist_id ON checklist_information (checklist_id)');
        $this->addSql('CREATE INDEX ix_checklist_information_created_by ON checklist_information (created_by)');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist_information ADD CONSTRAINT FK_3FB5A813B16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE checklist_information ADD CONSTRAINT FK_3FB5A813DE12AB56 FOREIGN KEY (created_by) REFERENCES dd_user (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD checklist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784B16D08A7 FOREIGN KEY (checklist_id) REFERENCES checklist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C42F7784B16D08A7 ON report (checklist_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE checklist_information DROP CONSTRAINT FK_3FB5A813B16D08A7');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784B16D08A7');
        $this->addSql('DROP TABLE checklist');
        $this->addSql('DROP TABLE checklist_information');
        $this->addSql('DROP INDEX UNIQ_C42F7784B16D08A7');
        $this->addSql('ALTER TABLE report DROP checklist_id');
    }
}

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

        $this->addSql('CREATE TABLE checklist (id SERIAL NOT NULL, report_id INT DEFAULT NULL, reportingPeriodAccurate VARCHAR(3) NOT NULL, contactDetailsUptoDate VARCHAR(3) NOT NULL, deputyFullNameAccurateinCasrec VARCHAR(3) NOT NULL, decisionsSatisfactory VARCHAR(3) NOT NULL, consultationsSatisfactory VARCHAR(3) NOT NULL, careArrangements VARCHAR(3) NOT NULL, assetsDeclaredAndManaged VARCHAR(3) NOT NULL, debtsManaged VARCHAR(3) NOT NULL, openClosingBalancesMatch VARCHAR(3) NOT NULL, accountsBalance VARCHAR(3) NOT NULL, moneyMovementsAcceptable VARCHAR(3) NOT NULL, bondAdequate VARCHAR(3) NOT NULL, bondOrderMatchCasrec VARCHAR(3) NOT NULL, futureSignificantFinancialDecisions VARCHAR(3) NOT NULL, hasDeputyRaisedConcerns VARCHAR(3) NOT NULL, caseWorkerSatisified VARCHAR(3) NOT NULL, decision TEXT NOT NULL, caseManagerName VARCHAR(255) NOT NULL, lodgingSummary TEXT NOT NULL, finalDecision VARCHAR(30) NOT NULL, furtherInformationReceived TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C696D2F4BD2A4C0 ON checklist (report_id)');
        $this->addSql('ALTER TABLE checklist ADD CONSTRAINT FK_5C696D2F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
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
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784B16D08A7');
        $this->addSql('DROP TABLE checklist');
        $this->addSql('DROP INDEX UNIQ_C42F7784B16D08A7');
        $this->addSql('ALTER TABLE report DROP checklist_id');
    }
}

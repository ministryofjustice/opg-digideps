<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1591';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE report ADD pfa_court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD hw_court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784D907E0D4 FOREIGN KEY (pfa_court_order_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77844E0A74D0 FOREIGN KEY (hw_court_order_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("
            UPDATE report r SET pfa_court_order_id = (
                SELECT MAX(cor.court_order_id)
                FROM court_order_report cor
                JOIN court_order co
                    ON cor.court_order_id = co.id
                WHERE
                    cor.report_id = r.id
                    AND co.order_type = 'pfa'
            )
        ");
        $this->addSql("
            UPDATE report r SET hw_court_order_id = (
                SELECT MAX(cor.court_order_id)
                FROM court_order_report cor
                JOIN court_order co
                    ON cor.court_order_id = co.id
                WHERE
                    cor.report_id = r.id
                    AND co.order_type = 'hw'
            )
        ");

        $this->addSql('CREATE INDEX IDX_C42F7784D907E0D4 ON report (pfa_court_order_id)');
        $this->addSql('CREATE INDEX IDX_C42F77844E0A74D0 ON report (hw_court_order_id)');

        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT fk_8a8e0c134bd2a4c0');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT fk_8a8e0c134bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id) ON DELETE SET NULL');
        $this->addSql('DELETE FROM report WHERE COALESCE(pfa_court_order_id, 0) <> COALESCE(hw_court_order_id, 0)');
        $this->addSql('ALTER TABLE satisfaction DROP CONSTRAINT fk_8a8e0c134bd2a4c0');
        $this->addSql('ALTER TABLE satisfaction ADD CONSTRAINT fk_8a8e0c134bd2a4c0 FOREIGN KEY (report_id) REFERENCES report(id)');
        //Ensure that there is at least one court_order per report and guard against putting one order in both slots by mistake.
        //Guarding against a hw order being put in the pfa slot and vice versa would require a trigger so it is only done in php for now.
        $this->addSql('ALTER TABLE report ADD CONSTRAINT report_court_order_pfa_hw_constraint CHECK ( COALESCE(pfa_court_order_id, 0) <> COALESCE(hw_court_order_id, 0))');

        $this->addSql('ALTER TABLE court_order_report DROP CONSTRAINT fk_7598c4b24bd2a4c0');
        $this->addSql('ALTER TABLE court_order_report DROP CONSTRAINT fk_7598c4b2a8d7d89c');
        $this->addSql('DROP TABLE court_order_report');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE court_order_report (court_order_id INT NOT NULL, report_id INT NOT NULL, PRIMARY KEY(court_order_id, report_id))');
        $this->addSql('CREATE INDEX idx_7598c4b24bd2a4c0 ON court_order_report (report_id)');
        $this->addSql('CREATE INDEX idx_7598c4b2a8d7d89c ON court_order_report (court_order_id)');
        $this->addSql('ALTER TABLE court_order_report ADD CONSTRAINT fk_7598c4b24bd2a4c0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_report ADD CONSTRAINT fk_7598c4b2a8d7d89c FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("
            INSERT INTO court_order_report (court_order_id, report_id)
            SELECT pfa_court_order_id, id
            FROM report
            WHERE pfa_court_order_id IS NOT NULL
        ");
        $this->addSql("
            INSERT INTO court_order_report (court_order_id, report_id)
            SELECT hw_court_order_id, id
            FROM report
            WHERE hw_court_order_id IS NOT NULL
        ");

        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784D907E0D4');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F77844E0A74D0');
        $this->addSql('DROP INDEX IDX_C42F7784D907E0D4');
        $this->addSql('DROP INDEX IDX_C42F77844E0A74D0');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT report_court_order_pfa_hw_constraint');
        $this->addSql('ALTER TABLE report DROP pfa_court_order_id');
        $this->addSql('ALTER TABLE report DROP hw_court_order_id');
    }
}

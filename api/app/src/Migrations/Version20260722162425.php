<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722162425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE court_order ADD report_type VARCHAR(7) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE court_order ALTER report_type DROP DEFAULT");

        $this->addSql('ALTER TABLE report ADD pfaCourtOrder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD hwCourtOrder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784D907E0D4 FOREIGN KEY (pfaCourtOrder_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77844E0A74D0 FOREIGN KEY (hwCourtOrder_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("
            UPDATE report SET pfaCourtOrder_id = MAX(cor.court_order_id)
            FROM court_order_report cor
            JOIN court_order co
                ON cor.court_order_id = co.id
            WHERE
                cor.report_id = id
                AND co.order_type = 'pfa'
        ");
        $this->addSql("
            UPDATE report SET hwCourtOrder_id = MAX(cor.court_order_id)
            FROM court_order_report cor
            JOIN court_order co
                ON cor.court_order_id = co.id
            WHERE
                cor.report_id = id
                AND co.order_type = 'hw'
        ");

        $this->addSql('CREATE INDEX IDX_C42F7784D907E0D4 ON report (pfaCourtOrder_id)');
        $this->addSql('CREATE INDEX IDX_C42F77844E0A74D0 ON report (hwCourtOrder_id)');

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
            SELECT pfaCourtOrder_id, id
            FROM report
            WHERE pfaCourtOrder_id IS NOT NULL
        ");
        $this->addSql("
            INSERT INTO court_order_report (court_order_id, report_id)
            SELECT hwCourtOrder_id, id
            FROM report
            WHERE hwCourtOrder_id IS NOT NULL
        ");

        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784D907E0D4');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F77844E0A74D0');
        $this->addSql('DROP INDEX IDX_C42F7784D907E0D4');
        $this->addSql('DROP INDEX IDX_C42F77844E0A74D0');
        $this->addSql('ALTER TABLE report DROP pfaCourtOrder_id');
        $this->addSql('ALTER TABLE report DROP hwCourtOrder_id');

        $this->addSql('ALTER TABLE court_order DROP report_type');
    }
}

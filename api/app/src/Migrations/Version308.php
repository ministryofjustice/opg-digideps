<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1353';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE court_order ALTER order_type TYPE VARCHAR(3)');

        $this->addSql('ALTER TABLE court_order ADD sibling_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE court_order ADD order_kind VARCHAR(6) NOT NULL DEFAULT \'single\'');
        $this->addSql('UPDATE court_order co SET order_kind = CASE WHEN d.is_hybrid = \'1\' THEN \'hybrid\' ELSE \'single\' END FROM staging.deputyship d where co.court_order_uid = d.order_uid');
        $this->addSql('ALTER TABLE court_order ALTER order_kind DROP DEFAULT');

        $this->addSql('ALTER TABLE court_order ADD order_report_type VARCHAR(6) NOT NULL DEFAULT \'\'');
        $this->addSql('UPDATE court_order co SET order_report_type = d.report_type FROM staging.deputyship d where co.court_order_uid = d.order_uid');
        $this->addSql('ALTER TABLE court_order ALTER order_report_type DROP DEFAULT');

        $this->addSql('ALTER TABLE staging.selectedcandidates ADD order_kind VARCHAR(6) DEFAULT NULL');

        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT FK_E824C0E6E4A463 FOREIGN KEY (sibling_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E824C0E6E4A463 ON court_order (sibling_id)');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('DROP INDEX UNIQ_E824C0E6E4A463');
        $this->addSql('ALTER TABLE court_order DROP CONSTRAINT FK_E824C0E6E4A463');

        $this->addSql('ALTER TABLE staging.selectedCandidates DROP order_kind');

        $this->addSql('ALTER TABLE court_order DROP order_report_type');
        $this->addSql('ALTER TABLE court_order DROP order_kind');
        $this->addSql('ALTER TABLE court_order DROP sibling_id');

        $this->addSql('ALTER TABLE court_order ALTER order_type TYPE VARCHAR(10)');
    }
}

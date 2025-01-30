<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version288 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE court_order_report (court_order_id INT NOT NULL, report_id INT NOT NULL, PRIMARY KEY(court_order_id, report_id))');
        $this->addSql('CREATE INDEX IDX_7598C4B2A8D7D89C ON court_order_report (court_order_id)');
        $this->addSql('CREATE INDEX IDX_7598C4B24BD2A4C0 ON court_order_report (report_id)');
        $this->addSql('ALTER TABLE court_order_report ADD CONSTRAINT FK_7598C4B2A8D7D89C FOREIGN KEY (court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE court_order_report ADD CONSTRAINT FK_7598C4B24BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order_report DROP CONSTRAINT FK_7598C4B2A8D7D89C');
        $this->addSql('ALTER TABLE court_order_report DROP CONSTRAINT FK_7598C4B24BD2A4C0');
        $this->addSql('DROP TABLE court_order_report');
    }
}

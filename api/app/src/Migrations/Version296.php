<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version296 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order ADD order_made_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD order_type VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE court_order RENAME COLUMN type TO order_type');
        $this->addSql('ALTER TABLE staging.selectedcandidates DROP order_updated_date');
        $this->addSql('ALTER TABLE staging.selectedcandidates DROP deputy_status_on_order');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD deputy_status_on_order BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order DROP order_made_date');
        $this->addSql('ALTER TABLE staging.selectedCandidates DROP order_type');
        $this->addSql('ALTER TABLE staging.selectedCandidates DROP order_id');
        $this->addSql('ALTER TABLE court_order RENAME COLUMN order_type TO type');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD order_updated_date VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE staging.selectedCandidates ADD deputy_status_on_order VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE staging.selectedcandidates DROP deputy_status_on_order');
    }
}

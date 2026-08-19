<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1591 II';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM report_submission WHERE report_id IS NULL');
        $this->addSql('ALTER INDEX idx_c42f7784d907e0d4 RENAME TO IDX_C42F77844EF621E1');
        $this->addSql('ALTER INDEX idx_c42f77844e0a74d0 RENAME TO IDX_C42F7784DDF824B5');
        $this->addSql('ALTER TABLE report_submission ALTER report_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_submission ALTER report_id DROP NOT NULL');
        $this->addSql('ALTER INDEX idx_c42f7784ddf824b5 RENAME TO idx_c42f77844e0a74d0');
        $this->addSql('ALTER INDEX idx_c42f77844ef621e1 RENAME TO idx_c42f7784d907e0d4');
    }
}

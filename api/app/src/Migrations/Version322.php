<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create columns preemptively';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM report WHERE client_id IS NULL');
        $this->addSql('ALTER TABLE report ALTER client_id SET NOT NULL');

        $this->addSql('ALTER TABLE report ADD pfa_court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD hw_court_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77844EF621E1 FOREIGN KEY (pfa_court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784DDF824B5 FOREIGN KEY (hw_court_order_id) REFERENCES court_order (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C42F77844EF621E1 ON report (pfa_court_order_id)');
        $this->addSql('CREATE INDEX IDX_C42F7784DDF824B5 ON report (hw_court_order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F77844EF621E1');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784DDF824B5');
        $this->addSql('DROP INDEX IDX_C42F77844EF621E1');
        $this->addSql('DROP INDEX IDX_C42F7784DDF824B5');
        $this->addSql('ALTER TABLE report DROP pfa_court_order_id');
        $this->addSql('ALTER TABLE report DROP hw_court_order_id');
        $this->addSql('ALTER TABLE report ALTER client_id DROP NOT NULL');
    }
}

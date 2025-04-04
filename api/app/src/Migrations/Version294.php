<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version294 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update court order table active column data type';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order RENAME COLUMN active TO status');
        $this->addSql('ALTER TABLE court_order ALTER status TYPE VARCHAR(10)');
        $this->addSql('ALTER TABLE court_order ALTER status DROP DEFAULT');
        $this->addSql('CREATE INDEX deputy_uid_idx ON staging.deputyship (deputy_uid)');
        $this->addSql('CREATE INDEX order_uid_idx ON staging.deputyship (order_uid)');
        $this->addSql('ALTER TABLE court_order_deputy RENAME COLUMN discharged TO is_active');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX deputy_uid_idx');
        $this->addSql('DROP INDEX order_uid_idx');
        $this->addSql('ALTER TABLE court_order ALTER status TYPE BOOLEAN');
        $this->addSql('ALTER TABLE court_order ALTER status SET DEFAULT true');
        $this->addSql('ALTER TABLE court_order ALTER status TYPE BOOLEAN');
        $this->addSql('ALTER TABLE court_order_deputy RENAME COLUMN is_active TO discharged');
        $this->addSql('ALTER TABLE court_order RENAME COLUMN status TO active');
    }
}

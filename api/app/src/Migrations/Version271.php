<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version271 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add No Money In columns to the cover the scenario when no money has been added to the client account';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report ADD money_in_exists TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD reason_for_no_money_in TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP money_in_exists');
        $this->addSql('ALTER TABLE report DROP reason_for_no_money_in');
    }
}

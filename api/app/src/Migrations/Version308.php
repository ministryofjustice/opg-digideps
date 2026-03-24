<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1353';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order ADD sibling_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE court_order ADD order_kind VARCHAR(6) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT FK_E824C0E6E4A463 FOREIGN KEY (sibling_id) REFERENCES court_order (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E824C0E6E4A463 ON court_order (sibling_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order DROP CONSTRAINT FK_E824C0E6E4A463');
        $this->addSql('DROP INDEX UNIQ_E824C0E6E4A463');
        $this->addSql('ALTER TABLE court_order DROP sibling_id');
        $this->addSql('ALTER TABLE court_order DROP order_kind');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version287 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order ADD client_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE court_order ADD CONSTRAINT FK_E824C019EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E824C019EB6921 ON court_order (client_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE court_order DROP CONSTRAINT FK_E824C019EB6921');
        $this->addSql('DROP INDEX IDX_E824C019EB6921');
        $this->addSql('ALTER TABLE court_order DROP client_id');
    }
}

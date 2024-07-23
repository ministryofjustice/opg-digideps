<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version281 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_deputy (client_id INT NOT NULL, deputy_id INT NOT NULL, PRIMARY KEY(client_id, deputy_id))');
        $this->addSql('CREATE INDEX IDX_7D5C202C19EB6921 ON client_deputy (client_id)');
        $this->addSql('CREATE INDEX IDX_7D5C202C4B6F93BB ON client_deputy (deputy_id)');
        $this->addSql('ALTER TABLE client_deputy ADD CONSTRAINT FK_7D5C202C19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client_deputy ADD CONSTRAINT FK_7D5C202C4B6F93BB FOREIGN KEY (deputy_id) REFERENCES deputy (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT fk_c744045595162e7c');
        $this->addSql('DROP INDEX idx_c744045595162e7c');
        $this->addSql('INSERT INTO client_deputy (client_id, deputy_id) SELECT id, deputy_id FROM client WHERE deputy_id IS NOT NULL');
        $this->addSql('ALTER TABLE client DROP deputy_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_deputy DROP CONSTRAINT FK_7D5C202C19EB6921');
        $this->addSql('ALTER TABLE client_deputy DROP CONSTRAINT FK_7D5C202C4B6F93BB');
        $this->addSql('DROP TABLE client_deputy');
        $this->addSql('ALTER TABLE client ADD deputy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT fk_c744045595162e7c FOREIGN KEY (deputy_id) REFERENCES deputy (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_c744045595162e7c ON client (deputy_id)');
    }
}

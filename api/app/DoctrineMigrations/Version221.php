<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version221 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE organisation_client');
        $this->addSql('ALTER TABLE client ADD organisation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404559E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C74404559E6B1585 ON client (organisation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE organisation_client (organisation_id INT NOT NULL, client_id INT NOT NULL, PRIMARY KEY(organisation_id, client_id))');
        $this->addSql('CREATE INDEX idx_455fde4819eb6921 ON organisation_client (client_id)');
        $this->addSql('CREATE INDEX idx_455fde489e6b1585 ON organisation_client (organisation_id)');
        $this->addSql('ALTER TABLE organisation_client ADD CONSTRAINT fk_455fde489e6b1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organisation_client ADD CONSTRAINT fk_455fde4819eb6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C74404559E6B1585');
        $this->addSql('DROP INDEX IDX_C74404559E6B1585');
        $this->addSql('ALTER TABLE client DROP organisation_id');
    }
}

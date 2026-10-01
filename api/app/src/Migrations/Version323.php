<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'sirius_organisation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staging.sirius_organisation (domain VARCHAR(60) NOT NULL, name VARCHAR(256) DEFAULT NULL, local_id INT DEFAULT NULL, PRIMARY KEY(domain))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE staging.sirius_organisation');
    }
}

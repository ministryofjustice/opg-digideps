<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version266 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add hybrid column to pre registration table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pre_registration ADD hybrid VARCHAR(6) DEFAULT NULL');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pre_registration DROP hybrid');

    }
}

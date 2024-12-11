<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version284 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjusts fk constraint so cleanup job can correctly execute';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT fk_28fa6b9fa76ed395;');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT fk_28fa6b9fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id) ON DELETE CASCADE;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deputy DROP CONSTRAINT fk_28fa6b9fa76ed395;');
        $this->addSql('ALTER TABLE deputy ADD CONSTRAINT fk_28fa6b9fa76ed395 FOREIGN KEY (user_id) REFERENCES dd_user (id);');
    }
}

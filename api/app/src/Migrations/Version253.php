<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Setting all users agree_terms_use to null so they are forced to view the new terms of service and agree.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE dd_user SET agree_terms_use = null WHERE role_name != 'ROLE_LAY_DEPUTY'");
        $this->addSql("UPDATE dd_user SET agree_terms_use_date = null WHERE role_name != 'ROLE_LAY_DEPUTY'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}

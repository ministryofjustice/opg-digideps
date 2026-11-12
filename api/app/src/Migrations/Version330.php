<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1420';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dd_user DROP deletion_protection');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dd_user ADD deletion_protection bool default null');
    }
}

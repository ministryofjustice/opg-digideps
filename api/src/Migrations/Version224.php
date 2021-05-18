<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update lay reports ending after 13/11/19 new due date of 21 days';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE report SET due_date = end_date + INTERVAL '21 days' WHERE end_date > '2019-11-12' AND
                       type IN ('102', '103', '104', '102-4', '103-4')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE report SET due_date = end_date + INTERVAL '56 days' WHERE end_date > '2019-11-12' AND
                       type IN ('102', '103', '104', '102-4', '103-4')");
    }
}

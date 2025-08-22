<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE staging.selectedcandidates ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE staging.selectedcandidates ALTER order_uid DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE staging.selectedCandidates DROP user_id');
        $this->addSql('ALTER TABLE staging.selectedCandidates ALTER order_uid SET NOT NULL');
    }
}

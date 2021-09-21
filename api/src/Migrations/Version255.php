<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add extra address fields to mirror casrec CSV';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_contact ADD address4 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE client_contact ADD address5 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address4 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE dd_user ADD address5 VARCHAR(200) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client_contact DROP address4');
        $this->addSql('ALTER TABLE client_contact DROP address5');
        $this->addSql('ALTER TABLE dd_user DROP address4');
        $this->addSql('ALTER TABLE dd_user DROP address5');
    }
}

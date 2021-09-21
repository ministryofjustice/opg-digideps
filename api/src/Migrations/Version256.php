<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Expands named_deputy:deputy_no length, adds unique constraint to named_deputy:deputy_no and drop existing named deputies to be recreated on next CSV run';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM named_deputy');
        $this->addSql('ALTER TABLE named_deputy ALTER deputy_no TYPE VARCHAR(20)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_105868993FCDCF4 ON named_deputy (deputy_no)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE named_deputy ALTER deputy_no TYPE VARCHAR(15)');
        $this->addSql('DROP INDEX UNIQ_105868993FCDCF4');
    }
}

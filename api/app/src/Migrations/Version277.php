<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version277 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename named_deputy table to deputy table and named_deputy_id to deputy_id in client table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE named_deputy RENAME TO deputy');
        $this->addSql('ALTER SEQUENCE named_deputy_id_seq RENAME TO deputy_id_seq');
        $this->addSql('ALTER INDEX named_deputy_uid_idx RENAME TO deputy_uid_idx');
        $this->addSql('ALTER TABLE client RENAME COLUMN named_deputy_id to deputy_id');
        $this->addSql('ALTER INDEX named_deputy_pkey RENAME to deputy_pkey;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deputy RENAME TO named_deputy');
        $this->addSql('ALTER SEQUENCE deputy_id_seq RENAME TO named_deputy_id_seq');
        $this->addSql('ALTER INDEX deputy_uid_idx RENAME TO named_deputy_uid_idx');
        $this->addSql('ALTER TABLE client RENAME COLUMN deputy_id to named_deputy_id');
        $this->addSql('ALTER INDEX deputy_pkey RENAME to named_deputy_pkey;');
    }
}

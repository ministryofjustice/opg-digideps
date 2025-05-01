<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version299 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase postcode fields to 20 characters, add id as primary key to deputyship staging table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.deputyship ADD id SERIAL NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.deputyship ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pre_registration ALTER deputy_postcode TYPE VARCHAR(20)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(20)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pre_registration ALTER client_postcode TYPE VARCHAR(10)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pre_registration ALTER deputy_postcode TYPE VARCHAR(10)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.deputyship DROP CONSTRAINT staging.deputyship_pkey
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE staging.deputyship DROP id
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX order_uid_idx ON staging.deputyship (order_uid)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX deputy_uid_idx ON staging.deputyship (deputy_uid)
        SQL);
    }
}

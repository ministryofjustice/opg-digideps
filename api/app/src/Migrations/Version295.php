<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version295 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX staging.order_uid_idx
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX staging.deputy_uid_idx
        SQL);

        $this->addSql('ALTER TABLE staging.deputyship DROP CONSTRAINT deputyship_pkey');

        $this->addSql('GRANT USAGE ON SCHEMA staging TO readonly_sql_user');

        $this->addSql('GRANT SELECT ON TABLE staging.deputyship TO readonly_sql_user');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE INDEX order_uid_idx ON staging.deputyship (order_uid)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX deputy_uid_idx ON staging.deputyship (deputy_uid)
        SQL);

        $this->addSql('ALTER TABLE staging.deputyship ADD PRIMARY KEY (deputy_uid, order_uid)');

        $this->addSql('REVOKE SELECT ON TABLE staging.deputyship FROM readonly_sql_user');

        $this->addSql('REVOKE USAGE ON SCHEMA staging FROM readonly_sql_user');
    }
}

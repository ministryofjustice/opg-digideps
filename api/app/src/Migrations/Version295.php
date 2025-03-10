<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version295 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add staging schema and deputyship staging table for CSV ingest';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA staging');
        $this->addSql('CREATE TABLE staging.deputyship (OrderUid VARCHAR(30) NOT NULL, DeputyUid VARCHAR(30) NOT NULL, OrderType VARCHAR(30) NOT NULL, OrderSubType VARCHAR(30) NOT NULL, OrderMadeDate VARCHAR(30) NOT NULL, OrderStatus VARCHAR(30) NOT NULL, OrderUpdatedDate VARCHAR(30) NOT NULL, CaseNumber VARCHAR(30) NOT NULL, ClientUid VARCHAR(30) NOT NULL, ClientStatus VARCHAR(30) NOT NULL, ClientStatusDate VARCHAR(30) NOT NULL, DeputyType VARCHAR(30) NOT NULL, DeputyStatusOnOrder VARCHAR(30) NOT NULL, DeputyStatusChangeDate VARCHAR(30) NOT NULL, ReportType VARCHAR(30) NOT NULL, IsHybrid VARCHAR(30) NOT NULL, PRIMARY KEY(OrderUid, DeputyUid))');
        $this->addSql('CREATE INDEX deputy_uid_idx ON staging.deputyship (DeputyUid)');
        $this->addSql('CREATE INDEX case_number_idx ON staging.deputyship (CaseNumber)');
        $this->addSql('CREATE INDEX order_uid_idx ON staging.deputyship (OrderUid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE staging.deputyship');
    }
}

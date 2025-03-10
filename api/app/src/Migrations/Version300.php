<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA staging');
        $this->addSql('CREATE TABLE staging.deputyship (OrderUid VARCHAR(30) NOT NULL, DeputyUid VARCHAR(30) NOT NULL, OrderType VARCHAR(30) DEFAULT NULL, OrderSubType VARCHAR(30) DEFAULT NULL, OrderMadeDate VARCHAR(30) DEFAULT NULL, OrderStatus VARCHAR(30) DEFAULT NULL, OrderUpdatedDate VARCHAR(30) DEFAULT NULL, CaseNumber VARCHAR(30) DEFAULT NULL, ClientUid VARCHAR(30) DEFAULT NULL, ClientStatus VARCHAR(30) DEFAULT NULL, ClientStatusDate VARCHAR(30) DEFAULT NULL, DeputyType VARCHAR(30) DEFAULT NULL, DeputyStatusOnOrder VARCHAR(30) DEFAULT NULL, DeputyStatusChangeDate VARCHAR(30) DEFAULT NULL, ReportType VARCHAR(30) DEFAULT NULL, IsHybrid VARCHAR(30) DEFAULT NULL, PRIMARY KEY(OrderUid, DeputyUid))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE staging.deputyship');
    }
}

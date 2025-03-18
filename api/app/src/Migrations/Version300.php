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
        $this->addSql('CREATE TABLE staging.deputyship (order_uid VARCHAR(30) NOT NULL, deputy_uid VARCHAR(30) NOT NULL, order_type VARCHAR(30) DEFAULT NULL, order_sub_type VARCHAR(30) DEFAULT NULL, order_made_date VARCHAR(30) DEFAULT NULL, order_status VARCHAR(30) DEFAULT NULL, order_updated_date VARCHAR(30) DEFAULT NULL, case_number VARCHAR(30) DEFAULT NULL, client_uid VARCHAR(30) DEFAULT NULL, client_status VARCHAR(30) DEFAULT NULL, client_status_date VARCHAR(30) DEFAULT NULL, deputy_type VARCHAR(30) DEFAULT NULL, deputy_status_on_order VARCHAR(30) DEFAULT NULL, deputy_status_change_date VARCHAR(30) DEFAULT NULL, report_type VARCHAR(30) DEFAULT NULL, is_hybrid VARCHAR(30) DEFAULT NULL, PRIMARY KEY(order_uid, deputy_uid))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE staging.deputyship');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'DDLS-1455';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE staging.lay_ingest (id INT NOT NULL, case_number VARCHAR(20) NOT NULL, client_first_name VARCHAR(50) NOT NULL, client_last_name VARCHAR(50) NOT NULL, client_address1 VARCHAR(200) NOT NULL, client_address2 VARCHAR(200) NOT NULL, client_address3 VARCHAR(200) NOT NULL, client_address4 VARCHAR(200) NOT NULL, client_address5 VARCHAR(200) NOT NULL, client_post_code VARCHAR(10) NOT NULL, deputy_uid VARCHAR(20) NOT NULL, deputy_first_name VARCHAR(100) NOT NULL, deputy_last_name VARCHAR(100) NOT NULL, deputy_address1 VARCHAR(200) NOT NULL, deputy_address2 VARCHAR(200) NOT NULL, deputy_address3 VARCHAR(200) NOT NULL, deputy_address4 VARCHAR(200) NOT NULL, deputy_address5 VARCHAR(200) NOT NULL, deputy_post_code VARCHAR(10) NOT NULL, report_type VARCHAR(6) NOT NULL, made_date DATE NOT NULL, order_type VARCHAR(3) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN staging.lay_ingest.made_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE staging.pa_pro_ingest (id INT NOT NULL, case_number VARCHAR(20) NOT NULL, client_first_name VARCHAR(50) NOT NULL, client_last_name VARCHAR(50) NOT NULL, client_date_of_birth DATE NOT NULL, client_address1 VARCHAR(200) NOT NULL, client_address2 VARCHAR(200) NOT NULL, client_address3 VARCHAR(200) NOT NULL, client_address4 VARCHAR(200) NOT NULL, client_address5 VARCHAR(200) NOT NULL, client_post_code VARCHAR(10) NOT NULL, deputy_type VARCHAR(3) NOT NULL, deputy_uid VARCHAR(20) NOT NULL, deputy_email VARCHAR(60) NOT NULL, deputy_organisation VARCHAR(100) NOT NULL, deputy_first_name VARCHAR(100) NOT NULL, deputy_last_name VARCHAR(100) NOT NULL, deputy_address1 VARCHAR(200) NOT NULL, deputy_address2 VARCHAR(200) NOT NULL, deputy_address3 VARCHAR(200) NOT NULL, deputy_address4 VARCHAR(200) NOT NULL, deputy_address5 VARCHAR(200) NOT NULL, deputy_post_code VARCHAR(10) NOT NULL, made_date DATE NOT NULL, report_type VARCHAR(6) NOT NULL, order_type VARCHAR(3) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN staging.pa_pro_ingest.client_date_of_birth IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN staging.pa_pro_ingest.made_date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE staging.lay_ingest');
        $this->addSql('DROP TABLE staging.pa_pro_ingest');
    }
}

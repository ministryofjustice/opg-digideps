<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version262 extends AbstractMigration
{
    public function getDescription(): string
    {
        return <<<DESC
- Rename casrec table to pre_registration
- Add new deputy address and deputy_uid fields
- Amend references of casrec to sirius in checklist columns
DESC;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE casrec_id_seq CASCADE');
        $this->addSql('CREATE TABLE pre_registration (id SERIAL NOT NULL, client_case_number VARCHAR(20) NOT NULL, client_lastname VARCHAR(50) NOT NULL, deputy_uid VARCHAR(100) NOT NULL, deputy_lastname VARCHAR(100) DEFAULT NULL, deputy_address_1 VARCHAR(255) DEFAULT NULL, deputy_address_2 VARCHAR(255) DEFAULT NULL, deputy_address_3 VARCHAR(255) DEFAULT NULL, deputy_address_4 VARCHAR(255) DEFAULT NULL, deputy_address_5 VARCHAR(255) DEFAULT NULL, deputy_postcode VARCHAR(10) DEFAULT NULL, type_of_report VARCHAR(10) DEFAULT NULL, ndr BOOLEAN DEFAULT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, order_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, order_type VARCHAR(255) DEFAULT NULL, is_co_deputy BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX updated_at_index ON pre_registration (updated_at)');
        $this->addSql('DROP TABLE casrec');
        $this->addSql('ALTER TABLE checklist RENAME deputy_full_name_accurate_in_casrec TO deputy_full_name_accurate_in_sirius');
        $this->addSql('ALTER TABLE checklist RENAME bond_order_match_casrec TO bond_order_match_sirius');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE casrec_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE casrec (id SERIAL NOT NULL, client_case_number VARCHAR(20) NOT NULL, client_lastname VARCHAR(50) NOT NULL, deputy_uid VARCHAR(100) NOT NULL, deputy_lastname VARCHAR(100) DEFAULT NULL, deputy_postcode VARCHAR(10) DEFAULT NULL, type_of_report VARCHAR(10) DEFAULT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, order_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deputy_address_1 VARCHAR(255) DEFAULT NULL, deputy_address_2 VARCHAR(255) DEFAULT NULL, deputy_address_3 VARCHAR(255) DEFAULT NULL, deputy_address_4 VARCHAR(255) DEFAULT NULL, deputy_address_5 VARCHAR(255) DEFAULT NULL, ndr BOOLEAN DEFAULT NULL, order_type VARCHAR(255) DEFAULT NULL, is_co_deputy BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX updated_at_index ON casrec (updated_at)');
        $this->addSql('DROP TABLE pre_registration');
        $this->addSql('ALTER TABLE checklist RENAME deputy_full_name_accurate_in_sirius TO deputy_full_name_accurate_in_casrec');
        $this->addSql('ALTER TABLE checklist RENAME bond_order_match_sirius TO bond_order_match_casrec');
    }
}

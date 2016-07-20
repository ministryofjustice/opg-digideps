<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version084 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE odr_asset (id SERIAL NOT NULL, odr_id INT DEFAULT NULL, asset_value NUMERIC(14, 2) DEFAULT NULL, last_edit TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) NOT NULL, address VARCHAR(200) DEFAULT NULL, address2 VARCHAR(200) DEFAULT NULL, county VARCHAR(75) DEFAULT NULL, postcode VARCHAR(10) DEFAULT NULL, occupants VARCHAR(550) DEFAULT NULL, owned VARCHAR(15) DEFAULT NULL, owned_percentage NUMERIC(14, 2) DEFAULT NULL, is_subject_equity_rel VARCHAR(4) DEFAULT NULL, has_mortgage VARCHAR(4) DEFAULT NULL, mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL, has_charges VARCHAR(4) DEFAULT NULL, is_rented_out VARCHAR(4) DEFAULT NULL, rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, rent_income_month NUMERIC(14, 2) DEFAULT NULL, title VARCHAR(100) DEFAULT NULL, description TEXT DEFAULT NULL, valuation_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73D022FB7CE4B994 ON odr_asset (odr_id)');
        $this->addSql('ALTER TABLE odr_asset ADD CONSTRAINT FK_73D022FB7CE4B994 FOREIGN KEY (odr_id) REFERENCES odr (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE odr ADD no_asset_to_add BOOLEAN DEFAULT \'false\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE odr_asset');
        $this->addSql('ALTER TABLE odr DROP no_asset_to_add');
    }
}

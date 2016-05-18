<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version061 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset ADD occupants VARCHAR(650) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD owned VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD owned_percentage NUMERIC(14, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD is_subject_equity_rel VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD has_mortgage VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD mortgage_outstanding NUMERIC(14, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD has_charges VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD is_rented_out VARCHAR(4) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD rent_agreement_end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD rent_income_month NUMERIC(14, 2) DEFAULT NULL');

        $this->addSql('ALTER TABLE asset ADD address VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD address2 VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD county VARCHAR(75) DEFAULT NULL');
        $this->addSql('ALTER TABLE asset ADD postcode VARCHAR(10) DEFAULT NULL');

        // property migration
        //TODO revise
        $this->addSql("UPDATE asset SET type='property', occupants=description WHERE title='Property'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset DROP occupants');
        $this->addSql('ALTER TABLE asset DROP owned');
        $this->addSql('ALTER TABLE asset DROP owned_percentage');
        $this->addSql('ALTER TABLE asset DROP is_subject_equity_rel');
        $this->addSql('ALTER TABLE asset DROP has_mortgage');
        $this->addSql('ALTER TABLE asset DROP mortgage_outstanding');
        $this->addSql('ALTER TABLE asset DROP has_charges');
        $this->addSql('ALTER TABLE asset DROP is_rented_out');
        $this->addSql('ALTER TABLE asset DROP rent_agreement_end_date');
        $this->addSql('ALTER TABLE asset DROP rent_income_month');

        $this->addSql('ALTER TABLE asset DROP type');
        $this->addSql('ALTER TABLE asset DROP address');
        $this->addSql('ALTER TABLE asset DROP address2');
        $this->addSql('ALTER TABLE asset DROP county');
        $this->addSql('ALTER TABLE asset DROP postcode');
    }
}

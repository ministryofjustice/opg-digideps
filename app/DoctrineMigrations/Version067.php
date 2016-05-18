<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version067 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE account ALTER bank_name TYPE VARCHAR(300)');

        // prepend account_type (if existing)
//        $this->addSql("UPDATE account SET bank_name = account_type || ' - ' || bank_name 
//            WHERE account_type IS NOT NULL AND account_type<> '' ");

        // set to "isa" if containing "isa" (Case insensitive)
        $this->addSql("UPDATE account SET account_type = 'isa' 
            WHERE account_type ILIKE '%isa%'  ");

        // set to "savings" if containing "saver" or "savings" (Case insensitive)
        $this->addSql("UPDATE account SET account_type = 'savings' 
            WHERE account_type ILIKE '%saver%' 
            OR account_type ILIKE '%saving%'
        ");

        // set to "savings" if containing "current" (Case insensitive)
        $this->addSql("UPDATE account SET account_type = 'current' 
            WHERE account_type ILIKE '%current%' 
        ");

        // set to "other" any type not mapped above
        $this->addSql("UPDATE account SET account_type = 'other' 
            WHERE account_type NOT IN ('isa', 'savings', 'current') or account_type is null ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no way back for this, data is cleaned and old data is not saved
    }
}

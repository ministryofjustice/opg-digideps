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
        // set to "isa" if containing "isa" (Case insensitive)
        $this->addSql("UPDATE account SET account_type = 'isa' 
            WHERE account_type ILIKE '%isa%'  ");
        
        // set to "savings" if containing "saver" or "savings" or "cashbuilder" (Case insensitive)
        $this->addSql("UPDATE account SET account_type = 'savings' 
            WHERE account_type ILIKE '%saver%' 
            OR account_type ILIKE '%saving%'
            OR account_type ILIKE '%cashbuilder%'
        ");
        
        // set to "current" all the others
        $this->addSql("UPDATE account SET account_type = 'current' 
            WHERE account_type NOT IN ('isa', 'savings') ");
        
        
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no way back for this, data is cleaned and old data is not saved
    }

}
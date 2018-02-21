<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version160 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('update report set no_transfers_to_add = false where id IN (
            select r.id from report r 
            LEFT JOIN money_transfer t on t.report_id = r.id 
            where (r.no_transfers_to_add IS NULL or r.no_transfers_to_add = true) 
            GROUP BY (r.id) HAVING count(t.id) > 0
         ) ;');


        /**
        After this query above, the following should return 0 results

        select r.id, r.no_transfers_to_add, count(t.id) from report r
        LEFT JOIN money_transfer t on t.report_id = r.id
        where (r.no_transfers_to_add IS NULL or r.no_transfers_to_add = true)
        GROUP BY (r.id) HAVING count(t.id) > 0 ;
         *
         */
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}

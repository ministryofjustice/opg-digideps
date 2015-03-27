<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE account_transaction_type SET has_more_details = true WHERE id='any_other_money_paid_out_and_not_listed_above';");

    }

    public function down(Schema $schema)
    {
        // no need to go back
    }
}

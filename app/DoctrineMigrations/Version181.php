<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version181 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $today = date('Y-m-d H:s');

        $this->addSql("UPDATE money_transaction SET category='anything-else-paid-out', description = rtrim('Other insurance - ' || description, '- '), meta = 'category other-insurance -> anything-else migrated on {$today}' WHERE category= 'other-insurance'; ");
        $this->addSql("UPDATE money_transaction SET category='anything-else-paid-out', description = rtrim('Household bills and expenses - ' || description, '- '), meta = 'category households-bills-other -> anything-else migrated on {$today}' WHERE category= 'households-bills-other'; ");
        $this->addSql("UPDATE money_transaction SET category='anything-else-paid-out', description = rtrim('Accomodations costs - ' || description, '- '), meta = 'category accommodation-other -> anything-else migrated on {$today}' WHERE category= 'accommodation-other'; ");
        $this->addSql("UPDATE money_transaction SET category='anything-else-paid-out', description = rtrim('OPG and other professional fees - ' || description, '- '), meta = 'category other-fees -> anything-else migrated on {$today}' WHERE category= 'other-fees'; ");
        $this->addSql("UPDATE money_transaction SET category='anything-else-paid-out', description = rtrim('Tax or charge - ' || description, '- '), meta = 'category debt-and-charges-other -> anything-else migrated on {$today}' WHERE category= 'debt-and-charges-other'; ");
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

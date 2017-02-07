<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version115 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $reportIds = $this->connection->query("select distinct(report_id) from money_transaction where category='gifts'")->fetchAll(\PDO::FETCH_COLUMN);


        $this->connection->beginTransaction();
        foreach (array_filter($reportIds) as $reportId) {

            $mts = $this->connection->fetchAll("SELECT * from money_transaction where category='gifts' AND report_id=" . $reportId);
            $gifts = $this->connection->fetchAll("SELECT * from gift WHERE report_id=" . $reportId);

            if (!$mts) continue;

            $this->connection->query("UPDATE report set gifts_exist='yes' WHERE id=$reportId ");

            foreach ($mts as $mt) {

                $existing = false;
                foreach ($gifts as $gift) {
                    if ($gift['amount'] != $mt['amount']) {
                        $existing = true;
                    }
                }

                if (!$existing) {
                    $insert = [
                        'report_id'   => $mt['report_id'],
                        'explanation' => $mt['description'],
                        'amount'      => $mt['amount'],
                    ];
                    $this->connection->insert('gift', $insert);
                }

            }

        }
        $this->connection->commit();

        $this->connection->query("DELETE FROM money_transaction where category='gifts'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

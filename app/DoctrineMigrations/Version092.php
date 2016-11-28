<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version092 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $sql = 'SELECT 
        t.id, 
        t.report_id as report_id, 
        t.more_details as description, t.amounts as amounts,
        tt.category as category
        FROM transaction t 
        LEFT JOIN transaction_type tt ON t.transaction_type_id = tt.id
        WHERE t.amounts is not null
        ';
        $oldTrans = $this->connection->fetchAll($sql);
        echo count($oldTrans)." to migrate: ";
        $this->connection->beginTransaction();

        $i = 0;
        foreach($oldTrans as $trans) {
            $newRecords = $this->convertOldToNew($trans);
            foreach($newRecords as $nr) {
                $this->connection->insert('money_transaction', $nr);
                if ($i % 100 == 0) {
                    echo ".";
                    $this->connection->commit();
                    $this->connection->beginTransaction();
                }
            }
        }
        $this->connection->commit();
    }

    private function convertOldToNew($old)
    {
        $amounts = explode(',', $old['amounts']);
        $ret = [];
        foreach($amounts as $amount) {
            $ret[] = [
                'report_id' => $old['report_id'],
                'category' => $old['category'],
                'amount' => $amount,
                'description' => $old['description'],
            ];
        }

        return $ret;
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

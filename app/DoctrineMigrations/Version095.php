<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version095 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        ini_set('memory_limit', '1024M');

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
        echo count($oldTrans) . " to migrate: ";
        $this->connection->beginTransaction();

        $i = 0;
        foreach ($oldTrans as $trans) {
            $i++;
            $newRecords = $this->convertOldToNew($trans);
            //print_r([$trans, $newRecords]);
            foreach ($newRecords as $nr) {
                $this->connection->insert('money_transaction', $nr);
                if (($i % 1000) == 0) {
                    echo "$i ";
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
        foreach ($amounts as $amount) {
            $ret[] = [
                'report_id' => $old['report_id'],
                'category' => $old['category'],
                'amount' => $amount,
                'description' => trim($old['description'], "\n\t. "),
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

        $this->addSql('delete from money_transaction');
    }
}

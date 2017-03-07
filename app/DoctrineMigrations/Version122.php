<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * https://opgtransform.atlassian.net/browse/DDPB-1046
 */
class Version122 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $expenses = $this->connection->query("select * from money_transaction where category='your-deputy-expenses'")->fetchAll();
        $this->connection->beginTransaction();
        foreach ($expenses as $expense) {
            $reportId=$expense['report_id'];
            $insert = [
                'report_id'   => $reportId,
                'explanation' => $expense['description'],
                'amount'      => $expense['amount'],
            ];
            $this->connection->insert('expense', $insert);
            $this->connection->query("update report set paid_for_anything ='yes' WHERE id=$reportId ");
        }

        $this->connection->query("delete from money_transaction where category='your-deputy-expenses'");

        $this->connection->commit();
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

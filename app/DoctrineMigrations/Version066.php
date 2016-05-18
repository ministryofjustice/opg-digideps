<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version066 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $twig = new \Twig_Environment(new \Twig_Loader_String());
         // merge value 1 into 2, resulting into 3
         $stmt = $this->connection->prepare(
            "UPDATE transaction 
                SET amounts = :amounts, 
                more_details = :more_details 
                WHERE report_id= :report_id
                AND transaction_type_id = 'anything-else'
                "
         );

        // select report with client name, the two transactions (with amount and more_details)
        // skip when there are no transfers in
        $reports = $this->connection->query("SELECT 
            r.id as report_id, c.firstname as client, 
            t1.amounts as amounts1, t1.more_details as moredet1, 
            t2.amounts as amounts2, t2.more_details as moredet2
            FROM report r 
            LEFT JOIN client c on r.client_id = c.id
            LEFT JOIN transaction t1 on t1.report_id = r.id 
            LEFT JOIN transaction t2 on t2.report_id = r.id 
            WHERE
            t1.transaction_type_id = 'transfers-in-from-client-s-other-accounts'
            AND
            t2.transaction_type_id = 'anything-else'
            AND
            t1.amounts IS NOT NULL
            ")->fetchAll();

        $this->connection->beginTransaction();

        foreach ($reports as &$row) {
            // calculate values
            $row['amounts3'] = implode(',', array_filter(array_merge(explode(',', $row['amounts2']), explode(',', $row['amounts1']))));
            $row['amounts1sum'] = array_sum(explode(',', $row['amounts1']));
            $row['amounts2sum'] = array_sum(explode(',', $row['amounts2']));
            // render new more_details
            $row['moredet3'] = $twig->render(
                "{% if moredet2 %}£{{ amounts2sum | number_format(2, '.', ',') }}: {{ moredet2 | raw }}\n{% endif %}"
                  ."Transfers in from {{client}}'s other accounts: £ {{ amounts1sum | number_format(2, '.', ',') }} - {{ moredet1 | raw }}",
                $row
              );
            $stmt->execute([
                'amounts' => $row['amounts3'],
                'more_details' => $row['moredet3'],
                'report_id' => $row['report_id'],
            ]);
        }

        $this->connection->query("DELETE FROM transaction WHERE transaction_type_id = 'transfers-in-from-client-s-other-accounts'");
        $this->connection->query("DELETE FROM transaction_type WHERE id = 'transfers-in-from-client-s-other-accounts'");

        $this->connection->commit();

        $this->addSql('SELECT MAX(version) from migrations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        echo 'migration 066 down does not restore the data';

        $this->addSql('SELECT MAX(version) from migrations');
    }
}

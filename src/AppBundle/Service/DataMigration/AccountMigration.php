<?php

namespace AppBundle\Service\DataMigration;

use Doctrine\DBAL\Connection;
use PDO;

class AccountMigration
{
    /**
     * @var Connection
     */
    private $pdo;


    private static $transactionMap = [

    ];


    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateAccounts()
    {
        $reports = $this->getReports();

        $oldTypes = $this->fetchAll("SELECT * from account_transaction_type");
        $newTypes = $this->fetchAll("SELECT * from transaction_type");

//        $toCsv = function($rows) {
//            $ret = '';
//            foreach($rows as $row) {
//                $ret .= implode(', ', $row) . "\n";
//            }
//            return $ret;
//        };
//        file_put_contents(__DIR__ . '/types.old.csv', $toCsv($oldTypes, true));
//        file_put_contents(__DIR__ . '/types.new.csv', $toCsv($newTypes, true));

        $stmt = $this->pdo->prepare(
            "INSERT INTO transaction(report_id, transaction_type_id, amount, more_details)"
            . " VALUES(:id, :transaction_type_id, :amount, :md)");

        foreach ($reports as $reportId => $reportData) {
            foreach ($reportData['accounts'] as $account) {
                foreach ($account['transactions_old'] as $typeId => $tRow) {
                    if (!isset(self::$transactionMap[$typeId])) {
                        throw new \RuntimeException("cannot map old transacition type [$typeId]");
                    }
                    $stmt->execute([
                        ':id' => $reportId,
                        ':transaction_type_id' => $typeId,
                        ':amount' => $tRow['amount'],
                        ':md' => $tRow['more_details'],
                    ]);
                }
            }
        }
    }


    public function getReports()
    {
        $reports = $this->fetchAll('SELECT * from report');

        foreach ($reports as $k => $report) {
            // add new transactions
            $reports[$k]['transactions_new'] =
                $this->fetchAll('SELECT * from transaction WHERE report_id = ' . $report['id']);
            $reports[$k]['transactions_new_sum'] =
                $this->calculateAmountsTotal($reports[$k]['transactions_new']);


            // add accounts
            $reports[$k]['accounts'] = $this->fetchAll('SELECT * from account WHERE report_id=' . $report['id']);

            // add old transaction to account
            foreach ($reports[$k]['accounts'] as $ka => $account) {
                $reports[$k]['accounts'][$ka]['transactions_old'] =
                    $this->fetchAll('SELECT * from account_transaction at
                        LEFT JOIN account_transaction_type att
                        ON at.account_transaction_type_id = att.id

                        WHERE account_id = ' . $account['id'], 'account_transaction_type_id');
                $reports[$k]['accounts'][$ka]['transactions_old_sum'] =
                    $this->calculateAmountsTotal($reports[$k]['accounts'][$ka]['transactions_old']);
            }
        }

        return $reports;
    }

    /**
     * @param array $transactions array of [type=>in/out, amount=>integer]
     * @param string $key
     * @return array of [in=>integer sum of the amount with type=in, out=>integer sum of the amount with type=out]
     */
    private function calculateAmountsTotal(array $transactions)
    {
        $ret = ['in' => 0.0, 'out' => 0.0];

        foreach ($transactions as $t) {
            $ret[$t['type']] += $t['amount'];
        }
        return $ret;
    }


    /**
     * Return query ASSOC results, using $key as ID
     * @param string $query
     * @param string $key
     *
     * @return array
     */
    private function fetchAll($query, $key = 'id')
    {
        $results = $this->pdo->query($query)->fetchAll();

        $ret = [];
        foreach ($results as $result) {
            $keyValue = $result[$key];
            $ret[$keyValue] = $result;
        }

        return $ret;
    }

}
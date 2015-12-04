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

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrateAccounts()
    {
        $data = $this->getReports();

    }

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

    public function getReports()
    {
        $reports = $this->fetchAll('SELECT * from report');

        foreach ($reports as $k => $report) {
            // add new transactions
            $reports[$k]['transactions_new'] =
                $this->fetchAll('SELECT * from transaction WHERE report_id = ' . $report['id']);
            $reports[$k]['transactions_new_sum'] =
                $this->calculateTotals($reports[$k]['transactions_new']);


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
                    $this->calculateTotals($reports[$k]['accounts'][$ka]['transactions_old']);
            }
        }

        return $reports;
    }

    private function calculateTotals(array $transactions, $key = 'amount')
    {
        $ret = ['in'=>0.0, 'out'=>0.0];

        foreach ($transactions as $t) {
            $ret[$t['type']] += $t['amount'];
        }
        return $ret;
    }

}
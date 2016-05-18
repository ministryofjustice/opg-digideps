<?php

use AppBundle\Entity\Account;
use AppBundle\Entity\AccountTransaction;
use AppBundle\Entity\Report;

/**
 * Used for unit testing.
 */
class Fixtures extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Report
     */
    public static function fillReport(Report $report, array $options)
    {
        $id = isset($options['id']) ? $options['id'] : rand(10000, 11000);

        $report->setId($id);

        return $report;
    }

    /**
     * @return Account
     */
    public static function fillAccount(Account $account, array $data)
    {
        $id = isset($data['id']) ? $data['id'] : rand(10000, 11000);

        $account->setId($id);
        $account->setBank(isset($data['bank']) ? $data['bank'] : "account {$id}");
        isset($data['report']) && $account->setReport($data['report']);

        foreach (['moneyIn' => 'setMoneyIn', 'moneyOut' => 'setMoneyOut'] as $key => $setter) {
            if (!isset($data[$key])) {
                continue;
            }
            $transactions = [];
            foreach ($data[$key] as $id => $amount) {
                $transactions[] = new AccountTransaction($id, $amount);
            }
            $account->$setter($transactions);
        }

        if (isset($data['closing'])) {
            $closingOptions = $data['closing'];
            $account->setClosingBalance($closingOptions['balance']);
            $account->setClosingDate($closingOptions['date']);
            $account->setClosingDateExplanation($closingOptions['dateExplanation']);
        }

        return $account;
    }
}

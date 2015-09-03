<?php

use AppBundle\Entity as EntityDir;

/**
 * Used for unit testing
 */
class Fixtures extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EntityDir\Report
     */
    public static function createReport(array $options)
    {
        $id = isset($options['id']) ? $options['id'] : rand(10000, 11000);
        
        $report = new EntityDir\Report;
        $report->setId($id);
        
        
        return $report;
    }
    
    /**
     * @return EntityDir\Account
     */
    public static function createAccount(array $options)
    {
        $id = isset($options['id']) ? $options['id'] : rand(10000, 11000);
        
        $account = new EntityDir\Account;
        $account->setId($id);
        $account->setBank(isset($options['bank']) ? $options['bank'] : "account {$id}");
        isset($options['report']) && $account->setReportObject($options['report']);
        
        foreach(['moneyIn'=>'setMoneyIn', 'moneyOut'=>'setMoneyOut'] as $key=>$setter) {
            if (!isset($options[$key])) {
                continue;
            }
            $transactions = [];
            foreach($options[$key] as $id=>$amount) {
                $transactions[] = new EntityDir\AccountTransaction($id, $amount);
            }
            $account->$setter($transactions);
        }
        
        if (isset($options['closing'])) {
             $closingOptions = $options['closing'];
             $account->setClosingBalance($closingOptions['balance']);
             $account->setClosingBalanceExplanation($closingOptions['balanceExplanation']);
             $account->setClosingDate($closingOptions['date']);
             $account->setClosingDateExplanation($closingOptions['dateExplanation']);
        }
        
        return $account;
    }
    
}
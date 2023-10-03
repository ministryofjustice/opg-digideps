<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportMoneyTransactionTrait
{
    /**
     * @JMS\Type("array<App\Entity\Report\MoneyTransaction>")
     * @JMS\Groups({"transactionsIn"})
     *
     * @var MoneyTransaction[]
     */
    private $moneyTransactionsIn = [];

    /**
     * @JMS\Type("array<App\Entity\Report\MoneyTransaction>")
     * @JMS\Groups({"transactionsOut"})
     *
     * @var MoneyTransaction[]
     */
    private $moneyTransactionsOut = [];

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $moneyInTotal;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $moneyOutTotal;

    /**
     * @param  MoneyTransaction[] $moneyTransactionsIn
     * @return Report
     */
    public function setMoneyTransactionsIn($moneyTransactionsIn)
    {
        $this->moneyTransactionsIn = $moneyTransactionsIn;

        return $this;
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsIn()
    {
        return $this->moneyTransactionsIn;
    }

    /**
     * @param  MoneyTransaction[] $moneyTransactionsOut
     * @return Report
     */
    public function setMoneyTransactionsOut($moneyTransactionsOut)
    {
        $this->moneyTransactionsOut = $moneyTransactionsOut;

        return $this;
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsOut()
    {
        return $this->moneyTransactionsOut;
    }

    /**
     * Group money transactions by Group
     *
     * @param MoneyTransaction[] $moneyTransactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupMoneyTransactionsByGroup(array $moneyTransactions)
    {
        $ret = [];

        foreach ($moneyTransactions as $id => $transaction) {
            $group = $transaction->getGroup();
            if (!isset($ret[$group])) {
                $ret[$group] = ['entries' => [], 'amountTotal' => 0];
            }
            $ret[$group]['entries'][$id] = $transaction; // needed to find the corresponding transaction in the form
            $ret[$group]['amountTotal'] += $transaction->getAmount();
        }

        return $ret;
    }

    /**
     * @return float
     */
    public function getMoneyInTotal()
    {
        return $this->moneyInTotal;
    }

    /**
     * @param float $moneyInTotal
     *
     * @return Report
     */
    public function setMoneyInTotal($moneyInTotal)
    {
        $this->moneyInTotal = $moneyInTotal;

        return $this;
    }

    /**
     * @return float
     */
    public function getMoneyOutTotal()
    {
        return $this->moneyOutTotal;
    }

    /**
     * @param float $moneyOutTotal
     *
     * @return Report
     */
    public function setMoneyOutTotal($moneyOutTotal)
    {
        $this->moneyOutTotal = $moneyOutTotal;

        return $this;
    }
}

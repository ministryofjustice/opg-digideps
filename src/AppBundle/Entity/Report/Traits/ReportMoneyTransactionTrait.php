<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;

trait ReportMoneyTransactionTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyTransaction>")
     * @JMS\Groups({"transactionsIn"})
     *
     * @var Transaction[]
     */
    private $moneyTransactionsIn;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyTransaction>")
     * @JMS\Groups({"transactionsOut"})
     *
     * @var Transaction[]
     */
    private $moneyTransactionsOut;

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
     * //TODO improve this
     *
     * @return Transaction[]
     */
//    public function getValidTransactions($moneyTransactions)
//    {
//        return array_filter($moneyTransactions, function($t){
//            return $t->getAmounts()[0] > 0;
//        });
//    }
//
//    /**
//     * //TODO improve this
//     * @return Transaction[]
//     */
//    public function getMoneyTransactionsInWithId($id)
//    {
//        return array_filter($this->moneyTra, function($t) use ($id) {
//            return $t->getId() == $id;
//        });
//    }

    /**
     * @param Transaction[] $moneyTransactionsIn
     */
    public function setMoneyTransactionsIn($moneyTransactionsIn)
    {
        $this->moneyTransactionsIn = $moneyTransactionsIn;

        return $this;
    }

    /**
     * @return Transaction[]
     */
    public function getMoneyTransactionsIn()
    {
        return $this->moneyTransactionsIn;
    }

    /**
     * @param Transaction[] $moneyTransactionsOut
     */
    public function setMoneyTransactionsOut($moneyTransactionsOut)
    {
        $this->moneyTransactionsOut = $moneyTransactionsOut;

        return $this;
    }

    /**
     * @return Transaction[]
     */
    public function getMoneyTransactionsOut()
    {
        return $this->moneyTransactionsOut;
    }

    /**
     * Group money transactions by Group
     *
     * @param Transaction[] $moneyTransactions
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
     */
    public function setMoneyOutTotal($moneyOutTotal)
    {
        $this->moneyOutTotal = $moneyOutTotal;

        return $this;
    }
}

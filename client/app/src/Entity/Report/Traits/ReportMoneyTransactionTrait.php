<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportMoneyTransactionTrait
{
    /**
     *
     * @var MoneyTransaction[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransaction>')]
    #[JMS\Groups(['transactionsIn'])]
    private array $moneyTransactionsIn = [];

    /**
     *
     * @var MoneyTransaction[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransaction>')]
    #[JMS\Groups(['transactionsOut'])]
    private array $moneyTransactionsOut = [];

    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $moneyInTotal;

    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $moneyOutTotal;

    /**
     * @param  MoneyTransaction[] $moneyTransactionsIn
     * @return Report
     */
    public function setMoneyTransactionsIn(array $moneyTransactionsIn): static
    {
        $this->moneyTransactionsIn = $moneyTransactionsIn;

        return $this;
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsIn(): array
    {
        return $this->moneyTransactionsIn;
    }

    /**
     * @param  MoneyTransaction[] $moneyTransactionsOut
     * @return Report
     */
    public function setMoneyTransactionsOut(array $moneyTransactionsOut): static
    {
        $this->moneyTransactionsOut = $moneyTransactionsOut;

        return $this;
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsOut(): array
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
            $ret[$group]['amountTotal'] += (float)$transaction->getAmount();
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
    public function setMoneyInTotal($moneyInTotal): static
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
    public function setMoneyOutTotal($moneyOutTotal): static
    {
        $this->moneyOutTotal = $moneyOutTotal;

        return $this;
    }
}

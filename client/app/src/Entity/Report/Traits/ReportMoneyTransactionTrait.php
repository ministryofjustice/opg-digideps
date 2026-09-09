<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransaction;

trait ReportMoneyTransactionTrait
{
    /**
     * @var array<MoneyTransaction>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransaction>')]
    #[JMS\Groups(['transactionsIn'])]
    private array $moneyTransactionsIn = [];

    /**
     * @var array<MoneyTransaction>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransaction>')]
    #[JMS\Groups(['transactionsOut'])]
    private array $moneyTransactionsOut = [];

    #[JMS\Type('double')]
    private ?float $moneyInTotal = null;

    #[JMS\Type('double')]
    private ?float $moneyOutTotal = null;

    /**
     * @param array<MoneyTransaction> $moneyTransactionsIn
     */
    public function setMoneyTransactionsIn(array $moneyTransactionsIn): static
    {
        $this->moneyTransactionsIn = $moneyTransactionsIn;

        return $this;
    }

    /**
     * @return array<MoneyTransaction>
     */
    public function getMoneyTransactionsIn(): array
    {
        return $this->moneyTransactionsIn;
    }

    /**
     * @param array<MoneyTransaction> $moneyTransactionsOut
     */
    public function setMoneyTransactionsOut(array $moneyTransactionsOut): static
    {
        $this->moneyTransactionsOut = $moneyTransactionsOut;

        return $this;
    }

    /**
     * @return array<MoneyTransaction>
     */
    public function getMoneyTransactionsOut(): array
    {
        return $this->moneyTransactionsOut;
    }

    /**
     * Group money transactions by Group
     *
     * @param array<MoneyTransaction> $moneyTransactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupMoneyTransactionsByGroup(array $moneyTransactions): array
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

    public function getMoneyInTotal(): ?float
    {
        return $this->moneyInTotal;
    }

    public function setMoneyInTotal(?float $moneyInTotal): static
    {
        $this->moneyInTotal = $moneyInTotal;

        return $this;
    }

    public function getMoneyOutTotal(): ?float
    {
        return $this->moneyOutTotal;
    }

    public function setMoneyOutTotal(?float $moneyOutTotal): static
    {
        $this->moneyOutTotal = $moneyOutTotal;

        return $this;
    }
}

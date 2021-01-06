<?php
namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\MoneyTransactionInterface;

trait MoneyTransactionTrait
{

    /**
     * @var MoneyTransaction[]
     *
     * @JMS\Groups({"transaction"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyTransaction", mappedBy="report", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyTransactions;

    /**
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_in")
     * @JMS\Groups({"transactionsIn"})
     *
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsIn()
    {
        return $this->moneyTransactions->filter(function ($t) {
            return $t->getType() == 'in';
        });
    }

    /**
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_out")
     * @JMS\Groups({"transactionsOut"})
     *
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactionsOut()
    {
        return $this->moneyTransactions->filter(function ($t) {
            return $t->getType() == 'out';
        });
    }

    /**
     * @return MoneyTransaction[]
     */
    public function getMoneyTransactions()
    {
        return $this->moneyTransactions;
    }

    /**
     * @param mixed $moneyTransactions
     */
    public function setMoneyTransactions($moneyTransactions)
    {
        $this->moneyTransactions = $moneyTransactions;
    }

    /**
     * @param mixed $moneyTransactions
     */
    public function addMoneyTransaction(MoneyTransaction $t)
    {
        if (!$this->moneyTransactions->contains($t)) {
            $this->moneyTransactions->add($t);
        }
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transactionsIn"})
     * @JMS\Type("double")
     * @JMS\SerializedName("money_in_total")
     */
    public function getMoneyInTotal()
    {
        return $this->getMoneyTransactionsTotal('in');
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"transactionsOut"})
     * @JMS\Type("double")
     * @JMS\SerializedName("money_out_total")
     */
    public function getMoneyOutTotal()
    {
        return $this->getMoneyTransactionsTotal('out');
    }

    /**
     * @param string $type in|put
     *
     * @return float
     */
    private function getMoneyTransactionsTotal($type)
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new \InvalidArgumentException('invalid type');
        }

        $ret = 0;

        if ($this->type === self::TYPE_103) {
            $transactions = $this->getMoneyTransactionsShort();
        } else {
            $transactions = $this->getMoneyTransactions();
        }

        foreach ($transactions as $t) {
            if ($t instanceof MoneyTransactionInterface && $t->getType() === $type) {
                $ret += $t->getAmount();
            }
        }

        return $ret;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupByCategory($transactions)
    {
        $ret = [];

        foreach ($transactions as $id => $t) {
            $cat = $t->getCategoryString();
            if (!isset($ret[$cat])) {
                $ret[$cat] = ['entries' => [], 'amountTotal' => 0];
            }
            $ret[$cat]['entries'][$id] = $t; // needed to find the corresponding transaction in the form
            $ret[$cat]['amountTotal'] += $t->getAmountsTotal();
        }

        return $ret;
    }

    /**
     ** @return bool
     */
    public function hasMoneyIn()
    {
        return count($this->getMoneyTransactionsIn()) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyOut()
    {
        return count($this->getMoneyTransactionsOut()) > 0;
    }
}

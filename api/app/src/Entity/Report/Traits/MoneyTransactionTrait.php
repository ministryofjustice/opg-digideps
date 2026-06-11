<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\MoneyTransaction;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionInterface;

trait MoneyTransactionTrait
{
    /**
     * @var Collection<int, MoneyTransaction>
     */
    #[JMS\Groups(['transaction'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyTransaction::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $moneyTransactions;

    /**
     * @return Collection<int, MoneyTransaction>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_in')]
    #[JMS\Groups(['transactionsIn'])]
    public function getMoneyTransactionsIn(): Collection
    {
        return $this->moneyTransactions->filter(function ($t): bool {
            return $t->getType() == 'in';
        });
    }

    /**
     * @return Collection<int, MoneyTransaction>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_out')]
    #[JMS\Groups(['transactionsOut'])]
    public function getMoneyTransactionsOut(): Collection
    {
        return $this->moneyTransactions->filter(function ($t): bool {
            return $t->getType() == 'out';
        });
    }

    /**
     * @return Collection<int, MoneyTransaction>
     */
    public function getMoneyTransactions(): Collection
    {
        return $this->moneyTransactions;
    }

    /**
     * @param Collection<int, MoneyTransaction> $moneyTransactions
     */
    public function setMoneyTransactions(Collection $moneyTransactions): void
    {
        $this->moneyTransactions = $moneyTransactions;
    }

    public function addMoneyTransaction(MoneyTransaction $t): void
    {
        if (!$this->moneyTransactions->contains($t)) {
            $this->moneyTransactions->add($t);
        }
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['transactionsIn'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('money_in_total')]
    public function getMoneyInTotal(): float
    {
        return $this->getMoneyTransactionsTotal('in');
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['transactionsOut'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('money_out_total')]
    public function getMoneyOutTotal(): float
    {
        return $this->getMoneyTransactionsTotal('out');
    }

    /**
     * @param string $type in|out
     */
    private function getMoneyTransactionsTotal(string $type): float
    {
        if (!in_array($type, ['in', 'out'])) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $ret = 0.0;

        if ($this->type === self::LAY_PFA_LOW_ASSETS_TYPE) {
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

    public function hasMoneyIn(): bool
    {
        return !$this->getMoneyTransactionsIn()->isEmpty();
    }

    public function hasMoneyOut(): bool
    {
        return !$this->getMoneyTransactionsOut()->isEmpty();
    }
}

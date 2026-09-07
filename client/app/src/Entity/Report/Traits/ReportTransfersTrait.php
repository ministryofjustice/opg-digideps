<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\MoneyTransfer;
use JMS\Serializer\Annotation as JMS;

trait ReportTransfersTrait
{
    /**
     * @var MoneyTransfer[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransfer>')]
    private array $moneyTransfers = [];

    #[JMS\Type('boolean')]
    #[JMS\Groups(['money-transfers-no-transfers'])]
    private ?bool $noTransfersToAdd = null;

    /**
     * Return list of money transfers by ID (as a proxy for creation date). Does not alter the ordering of the
     * underlying $this->moneyTransfers property.
     *
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers(): array
    {
        $moneyTransfers = [...$this->moneyTransfers];
        uasort($moneyTransfers, fn ($mt1, $mt2) => $mt1->getId() <=> $mt2->getId());
        return $moneyTransfers;
    }

    public function getMoneyTransferWithId(int $id): ?MoneyTransfer
    {
        return array_find($this->moneyTransfers, fn (MoneyTransfer $t) => $t->getId() == $id);
    }

    /**
     * @param MoneyTransfer[] $transfers
     */
    public function setMoneyTransfers(array $transfers): static
    {
        $this->moneyTransfers = $transfers;

        return $this;
    }

    public function getNoTransfersToAdd(): ?bool
    {
        return $this->noTransfersToAdd;
    }

    public function setNoTransfersToAdd(bool $noTransfersToAdd): static
    {
        $this->noTransfersToAdd = $noTransfersToAdd;
        return $this;
    }

    public function enoughBankAccountForTransfers(): bool
    {
        return count($this->getBankAccounts()) >= 2;
    }
}

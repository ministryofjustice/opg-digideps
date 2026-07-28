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

    /**
     *
     * @var bool
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['money-transfers-no-transfers'])]
    private $noTransfersToAdd;

    /**
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers(): array
    {
        return $this->moneyTransfers;
    }

    public function getMoneyTransferWithId($id): ?MoneyTransfer
    {
        return array_find($this->moneyTransfers, fn ($t) => $t->getId() == $id);
    }

    /**
     * @param MoneyTransfer[] $transfers
     */
    public function setMoneyTransfers(array $transfers)
    {
        $this->moneyTransfers = $transfers;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNoTransfersToAdd()
    {
        return $this->noTransfersToAdd;
    }

    /**
     * @param bool $noTransfersToAdd
     *
     * @return $this
     */
    public function setNoTransfersToAdd($noTransfersToAdd): static
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }

    public function enoughBankAccountForTransfers(): bool
    {
        return count($this->getBankAccounts()) >= 2;
    }
}

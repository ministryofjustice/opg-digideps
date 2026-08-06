<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\MoneyTransfer;
use JMS\Serializer\Annotation as JMS;

trait ReportTransfersTrait
{
    /**
     * @JMS\Type("array<OPG\Digideps\Frontend\Entity\Report\MoneyTransfer>")
     *
     * @var MoneyTransfer[]
     */
    private $moneyTransfers = [];

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"money-transfers-no-transfers"})
     *
     * @var bool
     */
    private $noTransfersToAdd;

    /**
     * Return list of money transfers by ID (as a proxy for creation date). Does not alter the ordering of the
     * underlying $this->moneyTransfers property.
     *
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers()
    {
        $moneyTransfers = [] + $this->moneyTransfers;
        uasort($moneyTransfers, fn ($mt1, $mt2) => $mt1->getId() <=> $mt2->getId());
        return $moneyTransfers;
    }

    /**
     * @return MoneyTransfer|null
     */
    public function getMoneyTransferWithId($id)
    {
        foreach ($this->moneyTransfers as $t) {
            if ($t->getId() == $id) {
                return $t;
            }
        }

        return null;
    }

    /**
     * @return $this
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
    public function setNoTransfersToAdd($noTransfersToAdd)
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }

    /**
     * @return bool
     */
    public function enoughBankAccountForTransfers()
    {
        return count($this->getBankAccounts()) >= 2;
    }
}

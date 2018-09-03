<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyTransfer;
use JMS\Serializer\Annotation as JMS;

trait ReportTransfersTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyTransfer>")
     *
     * @var MoneyTransfer[]
     */
    private $moneyTransfers;


    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"money-transfers-no-transfers"})
     *
     * @var bool
     */
    private $noTransfersToAdd;


    /**
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers()
    {
        return $this->moneyTransfers;
    }

    /**
     * @return MoneyTransfer
     */
    public function getMoneyTransferWithId($id)
    {
        foreach ($this->moneyTransfers as $t) {
            if ($t->getId() == $id) {
                return $t;
            }
        }

        return;
    }


    /**
     * @param  array $transfers
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
     * @param  bool  $noTransfersToAdd
     * @return $this
     */
    public function setNoTransfersToAdd($noTransfersToAdd)
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }

    /**
     * @return boolean
     */
    public function enoughBankAccountForTransfers()
    {
        return count($this->getBankAccounts()) >= 2;
    }
}

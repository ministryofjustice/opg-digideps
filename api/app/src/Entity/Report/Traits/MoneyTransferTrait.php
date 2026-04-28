<?php

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use OPG\Digideps\Backend\Entity\Report\MoneyTransfer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Report;

trait MoneyTransferTrait
{
    /**
     * @JMS\Groups({"money-transfer"})
     *
     * @JMS\Type("ArrayCollection<OPG\Digideps\Backend\Entity\Report\MoneyTransfer>")
     *
     * @ORM\OneToMany(targetEntity="OPG\Digideps\Backend\Entity\Report\MoneyTransfer", mappedBy="report", cascade={"persist"})
     */
    private $moneyTransfers;

    /**
     * @var bool deputy declaration saying there are no transfers. Required (true/false) if no transfers are added
     *
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"report", "money-transfer"})
     *
     * @ORM\Column(name="no_transfers_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    private $noTransfersToAdd;

    /**
     * @return ArrayCollection
     */
    public function getMoneyTransfers()
    {
        return $this->moneyTransfers;
    }

    /**
     * @return Report
     */
    public function addMoneyTransfers(MoneyTransfer $moneyTransfer)
    {
        $this->moneyTransfers->add($moneyTransfer);

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
     */
    public function setNoTransfersToAdd($noTransfersToAdd)
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }
}

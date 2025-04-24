<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\MoneyTransfer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait MoneyTransferTrait
{
    /**
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\MoneyTransfer", mappedBy="report", cascade={"persist"})
     */
    #[JMS\Groups(['money-transfer'])]
    #[JMS\Type('ArrayCollection<App\Entity\Report\MoneyTransfer>')]
    private $moneyTransfers;

    /**
     * @var bool deputy declaration saying there are no transfers. Required (true/false) if no transfers are added
     *
     *
     *
     * @ORM\Column(name="no_transfers_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['report', 'money-transfer'])]
    private $noTransfersToAdd;

    /**
     * @return ArrayCollection
     */
    public function getMoneyTransfers()
    {
        return $this->moneyTransfers;
    }

    /**
     * @return \Report
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

<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\MoneyTransfer;

trait MoneyTransferTrait
{
    /**
     * @var Collection<int, MoneyTransfer> $moneyTransfers
     */
    #[JMS\Groups(['money-transfer'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\MoneyTransfer>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyTransfer::class, cascade: ['persist'])]
    private Collection $moneyTransfers;

    /**
     * Deputy declaration saying there are no transfers. Required (true/false) if no transfers are added.
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['report', 'money-transfer'])]
    #[ORM\Column(name: 'no_transfers_to_add', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $noTransfersToAdd = false;

    /**
     * @return Collection<int, MoneyTransfer>
     */
    public function getMoneyTransfers(): Collection
    {
        return $this->moneyTransfers;
    }

    public function addMoneyTransfers(MoneyTransfer $moneyTransfer): static
    {
        $this->moneyTransfers->add($moneyTransfer);

        return $this;
    }

    public function getNoTransfersToAdd(): ?bool
    {
        return $this->noTransfersToAdd;
    }

    public function setNoTransfersToAdd(?bool $noTransfersToAdd): static
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }
}

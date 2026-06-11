<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    #[JMS\Groups(['account'])]
    #[JMS\SerializedName('bankAccount')]
    #[ORM\JoinColumn(name: 'bank_account_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\BankAccount')]
    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
    private ?BankAccount $bankAccount = null;

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): static
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }
}

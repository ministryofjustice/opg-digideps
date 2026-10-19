<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;

trait HasBankAccountTrait
{
    #[JMS\SerializedName('bankAccount')]
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\BankAccount')]
    #[JMS\Groups(['account'])]
    private ?BankAccount $bankAccount = null;

    #[JMS\Type('integer')]
    #[JMS\Groups(['account'])]
    private ?int $bankAccountId = null;

    public function getBankAccount(): ?BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?BankAccount $bankAccount): static
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }

    public function getBankAccountId(): ?int
    {
        return $this->bankAccountId;
    }

    public function setBankAccountId(?int $bankAccountId): static
    {
        $this->bankAccountId = $bankAccountId;
        return $this;
    }
}

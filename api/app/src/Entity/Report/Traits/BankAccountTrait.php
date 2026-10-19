<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\BankAccount;

trait BankAccountTrait
{
    /**
     * @var Collection<int, BankAccount>
     */
    #[JMS\Groups(['account'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\BankAccount>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: BankAccount::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $bankAccounts;

    public function addAccount(BankAccount $accounts): static
    {
        $this->bankAccounts[] = $accounts;

        return $this;
    }

    public function removeAccount(BankAccount $accounts): void
    {
        $this->bankAccounts->removeElement($accounts);
    }

    /**
     * @return Collection<int, BankAccount>
     */
    public function getBankAccounts(): Collection
    {
        return $this->bankAccounts;
    }

    /**
     * @return Collection<int, BankAccount>
     */
    public function getBankAccountsIncomplete(): Collection
    {
        return $this->bankAccounts->filter(function ($b): bool {
            return $b->getClosingBalance() === null;
        });
    }

    public function hasAccounts(): bool
    {
        return count($this->getBankAccounts()) > 0;
    }
}

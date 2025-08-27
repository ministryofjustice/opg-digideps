<?php

namespace App\Entity\Report\Traits;

use App\Entity\BankAccountInterface;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Report;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait BankAccountTrait
{
    /**
     * @var Collection<int, BankAccountInterface>|BankAccountInterface[]
     *
     * @JMS\Groups({"account"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Report\BankAccount>")
     */
    #[ORM\OneToMany(targetEntity: BankAccount::class, mappedBy: 'report', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection|array $bankAccounts;

    /**
     * Add accounts.
     *
     * @return Report
     */
    public function addAccount(BankAccount $accounts)
    {
        $this->bankAccounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts.
     */
    public function removeAccount(BankAccount $accounts)
    {
        $this->bankAccounts->removeElement($accounts);
    }

    /**
     * @return Collection<int, BankAccountInterface>|BankAccountInterface[]
     */
    public function getBankAccounts(): Collection|array
    {
        return $this->bankAccounts;
    }

    /**
     * @return Collection<int, BankAccountInterface>|BankAccountInterface[]
     */
    public function getBankAccountsIncomplete(): Collection|array
    {
        return $this->getBankAccounts()->filter(function ($b) {
            return null == $b->getClosingBalance();
        });
    }

    /**
     ** @return bool
     */
    public function hasAccounts()
    {
        return count($this->getBankAccounts()) > 0;
    }
}

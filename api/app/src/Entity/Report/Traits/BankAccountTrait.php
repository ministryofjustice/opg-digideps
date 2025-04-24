<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\BankAccount;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait BankAccountTrait
{
    /**
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\BankAccount", mappedBy="report", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"id" = "ASC"})
     */
    #[JMS\Groups(['account'])]
    #[JMS\Type('ArrayCollection<App\Entity\Report\BankAccount>')]
    private $bankAccounts;

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
     * Get accounts.
     *
     * @return BankAccount[]
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @return BankAccount[]
     */
    public function getBankAccountsIncomplete()
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

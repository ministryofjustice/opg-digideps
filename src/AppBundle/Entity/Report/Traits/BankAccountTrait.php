<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait BankAccountTrait
{

    /**
     * @JMS\Groups({"account"})
     * @JMS\Type("array<AppBundle\Entity\Report\BankAccount>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\BankAccount", mappedBy="report", cascade={"persist", "remove"})
     */
    private $bankAccounts;

    /**
     * Add accounts.
     *
     * @param BankAccount $accounts
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
     *
     * @param BankAccount $accounts
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
            return $b->getClosingBalance() == null;
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

<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\AssetOther;
use AppBundle\Entity\Report\AssetProperty;
use JMS\Serializer\Annotation as JMS;

trait ReportBankAccountsTrait
{

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\BankAccount>")
     *
     * @var BankAccount[]
     */
    private $bankAccounts;


    /**
     * @param array $bankAccounts
     *
     * @return \AppBundle\Entity\Report
     */
    public function setBankAccounts($bankAccounts)
    {
        foreach ($bankAccounts as $account) {
            $account->setReport($this);
        }

        $this->bankAccounts = $bankAccounts;

        return $this;
    }

    /**
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
        return array_filter($this->bankAccounts ?: [], function($b) {
            return $b->getClosingBalance() === null;
        });
    }

    /**
     * @return BankAccount
     */
    public function getBankAccountById($id)
    {
        foreach ($this->bankAccounts as $account) {
            if ($account->getId() == $id) {
                return $account;
            }
        }
    }
}

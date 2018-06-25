<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportBankAccountsTrait
{

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\BankAccount>")
     *
     * @var BankAccount[]
     */
    private $bankAccounts = [];

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $accountsClosingBalanceTotal;


    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $accountsOpeningBalanceTotal;

    /**
     * @param array $bankAccounts
     *
     * @return Report
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
        return array_filter($this->bankAccounts ?: [], function ($b) {
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

    /**
     * @return float
     */
    public function getAccountsClosingBalanceTotal()
    {
        return $this->accountsClosingBalanceTotal;
    }

    /**
     * @param float $accountsClosingBalanceTotal
     *
     * @return Report
     */
    public function setAccountsClosingBalanceTotal($accountsClosingBalanceTotal)
    {
        $this->accountsClosingBalanceTotal = $accountsClosingBalanceTotal;

        return $this;
    }

    /**
     ** @return bool
     */
    public function hasMoneyIn()
    {
        return count($this->getMoneyTransactionsIn()) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyOut()
    {
        return count($this->getMoneyTransactionsOut()) > 0;
    }

    /**
     * @return float
     */
    public function getAccountsOpeningBalanceTotal()
    {
        return $this->accountsOpeningBalanceTotal;
    }

    /**
     * @param float $accountsOpeningBalanceTotal
     */
    public function setAccountsOpeningBalanceTotal($accountsOpeningBalanceTotal)
    {
        $this->accountsOpeningBalanceTotal = $accountsOpeningBalanceTotal;
    }

    /**
     * Returns a formatted list of bank accounts associated with this report
     *
     * @return array
     */
    public function getBankAccountOptions()
    {
        $banksList = [];
        $banks = $this->getBankAccounts();
        foreach ($banks as $bank) {
            /* @var $bank BankAccount */
            $bankName = (!empty($bank->getBank()) ? $bank->getBank() . ' - '  : '') . $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
            $banksList[$bankName] = $bank->getId();
        }

        return $banksList;
    }
}

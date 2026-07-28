<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\BankAccount;
use OPG\Digideps\Frontend\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;

trait ReportBankAccountsTrait
{
    /**
     * @var BankAccount[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\BankAccount>')]
    private array $bankAccounts = [];

    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $accountsClosingBalanceTotal;


    /**
     * @var float
     */
    #[JMS\Type('double')]
    private $accountsOpeningBalanceTotal;

    public function setBankAccounts(array $bankAccounts): static
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
    public function getBankAccounts(): array
    {
        return $this->bankAccounts;
    }

    /**
     * @return BankAccount[]
     */
    public function getBankAccountsIncomplete(): array
    {
        return array_filter($this->bankAccounts ?: [], function ($b): bool {
            return $b->getClosingBalance() === null;
        });
    }

    public function getBankAccountById(int $id): ?BankAccount
    {
        return array_find($this->bankAccounts, fn ($account) => $account->getId() == $id);
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
     */
    public function setAccountsClosingBalanceTotal($accountsClosingBalanceTotal): static
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
    public function setAccountsOpeningBalanceTotal($accountsOpeningBalanceTotal): void
    {
        $this->accountsOpeningBalanceTotal = $accountsOpeningBalanceTotal;
    }

    /**
     * Returns a formatted list of bank accounts associated with this report
     *
     * @return array<string, int>
     */
    public function getBankAccountOptions(): array
    {
        $banksList = [];
        $banks = $this->getBankAccounts();
        foreach ($banks as $bank) {
            /* @var $bank BankAccount */
            $bankName = (!empty($bank->getBank()) ? $bank->getBank() . ' - ' : '') . $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
            $banksList[$bankName] = $bank->getId();
        }

        return $banksList;
    }
}

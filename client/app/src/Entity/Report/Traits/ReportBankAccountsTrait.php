<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;

trait ReportBankAccountsTrait
{
    /**
     * @var BankAccount[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\BankAccount>')]
    private array $bankAccounts = [];

    #[JMS\Type('double')]
    private float $accountsClosingBalanceTotal;

    #[JMS\Type('double')]
    private float $accountsOpeningBalanceTotal;

    /**
     * @param BankAccount[] $bankAccounts
     */
    public function setBankAccounts(array $bankAccounts): static
    {
        foreach ($bankAccounts as $account) {
            $account->setReport($this);
        }

        $this->bankAccounts = $bankAccounts;

        return $this;
    }

    /**
     * @return array<BankAccount>
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

    public function getAccountsClosingBalanceTotal(): float
    {
        return $this->accountsClosingBalanceTotal;
    }

    public function setAccountsClosingBalanceTotal(float $accountsClosingBalanceTotal): static
    {
        $this->accountsClosingBalanceTotal = $accountsClosingBalanceTotal;

        return $this;
    }

    public function hasMoneyIn(): bool
    {
        return count($this->getMoneyTransactionsIn()) > 0;
    }

    public function hasMoneyOut(): bool
    {
        return count($this->getMoneyTransactionsOut()) > 0;
    }

    public function getAccountsOpeningBalanceTotal(): float
    {
        return $this->accountsOpeningBalanceTotal;
    }

    public function setAccountsOpeningBalanceTotal(float $accountsOpeningBalanceTotal): static
    {
        $this->accountsOpeningBalanceTotal = $accountsOpeningBalanceTotal;

        return $this;
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
            if ($bank->getId() === null) {
                continue;
            }

            $bankName = ($bank->getBank() === null ? '' : $bank->getBank() . ' - ') .
                $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
            $banksList[$bankName] = $bank->getId();
        }

        return $banksList;
    }
}

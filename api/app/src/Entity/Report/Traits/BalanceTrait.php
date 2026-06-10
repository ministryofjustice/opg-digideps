<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Report;

trait BalanceTrait
{
    /**
     * Reason required if balance calculation mismatches
     */
    #[JMS\Groups(['balance'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'balance_mismatch_explanation', type: 'text', nullable: true)]
    private ?string $balanceMismatchExplanation = null;

    public function getBalanceMismatchExplanation(): ?string
    {
        return $this->balanceMismatchExplanation;
    }

    public function setBalanceMismatchExplanation(?string $balanceMismatchExplanation): void
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['balance', 'account'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('accounts_opening_balance_total')]
    public function getAccountsOpeningBalanceTotal(): float
    {
        $ret = 0.0;
        foreach ($this->getBankAccounts() as $a) {
            $ret += (float)($a->getOpeningBalance() ?? 0.0);
        }

        return $ret;
    }

    /**
     * Return sum of closing balances (if all of them have a value, otherwise returns null).
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['balance', 'account'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('accounts_closing_balance_total')]
    public function getAccountsClosingBalanceTotal(): float
    {
        $ret = 0.0;
        foreach ($this->getBankAccounts() as $a) {
            $ret += (float)$a->getClosingBalance();
        }

        return $ret;
    }

    /**
     * Calculate the balance
     * Account opening balance
     * + money in
     * - money out
     * - expense (that includes Fees for PAs)
     * - gifts.
     *
     * Return null if any of the opening balance is null
     * (that shouldn't be allowed anymore after a recent change. Refactor when/if convenient)
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['balance'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('calculated_balance')]
    public function getCalculatedBalance(): float
    {
        return $this->getAccountsOpeningBalanceTotal()
            + $this->getMoneyInTotal()
            - $this->getMoneyOutTotal()
            - ($this->hasSection(Report::SECTION_PROF_DEPUTY_COSTS) ? $this->getProfDeputyTotalCosts() ?? 0.0 : 0.0)
            - ($this->has106Flag() ? $this->getFeesTotal() : 0.0)
            - $this->getExpensesTotal()
            - $this->getGiftsTotal();
    }

    /**
     * Return the difference between getCalculatedBalance and getAccountsClosingBalanceTotal
     * if 0, then the report financial section are balanced
     * accounts are balanced and ready for submission.
     *
     * Return null if sections are not ready or closing accounts are not added yet
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['balance'])]
    #[JMS\Type('double')]
    #[JMS\SerializedName('totals_offset')]
    public function getTotalsOffset(): float
    {
        return $this->getCalculatedBalance() - $this->getAccountsClosingBalanceTotal();
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['balance'])]
    #[JMS\Type('boolean')]
    #[JMS\SerializedName('totals_match')]
    public function getTotalsMatch(): bool
    {
        return abs($this->getTotalsOffset()) < 0.2;
    }
}

<?php

namespace AppBundle\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait BalanceTrait
{

    /**
     * @var string reason required if balance calculation mismatches
     *
     * @JMS\Groups({"balance"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="balance_mismatch_explanation", type="text", nullable=true)
     */
    private $balanceMismatchExplanation;

    /**
     * @return string
     */
    public function getBalanceMismatchExplanation()
    {
        return $this->balanceMismatchExplanation;
    }

    /**
     * @param string $balanceMismatchExplanation
     */
    public function setBalanceMismatchExplanation($balanceMismatchExplanation)
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance", "account"})
     * @JMS\Type("double")
     * @JMS\SerializedName("accounts_opening_balance_total")
     */
    public function getAccountsOpeningBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $a) {
            if ($a->getOpeningBalance() === null) {
                return;
            }
            $ret += $a->getOpeningBalance();
        }

        return $ret;
    }

    /**
     * Return sum of closing balances (if all of them have a value, otherwise returns null).
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance", "account"})
     * @JMS\Type("double")
     * @JMS\SerializedName("accounts_closing_balance_total")
     *
     * @return float
     */
    public function getAccountsClosingBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $a) {
            if ($a->getClosingBalance() === null) {
                return;
            }
            $ret += $a->getClosingBalance();
        }

        return $ret;
    }

    /**
     * Calculate the balance
     * Account opening balance
     * + money in
     * - money out
     * - expense (that includes Fees for PAs)
     * - gifts
     *
     * Return null if any of the opening balance is null
     * (that shouldn't be allowed anymore after a recent change. Refactor when/if convenient)
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("double")
     * @JMS\SerializedName("calculated_balance")
     */
    public function getCalculatedBalance()
    {
        if ($this->getAccountsOpeningBalanceTotal() === null) {
            return null;
        }

        return $this->getAccountsOpeningBalanceTotal()
            + $this->getMoneyInTotal()
            - $this->getMoneyOutTotal()
            - ($this->has106Flag() ? $this->getFeesTotal() : 0)
            - $this->getExpensesTotal()
            - $this->getGiftsTotal();
        ;
    }

    /**
     * Return the difference between getCalculatedBalance and getAccountsClosingBalanceTotal
     * if 0, then the report financial section are balanced
     * accounts are balanced and ready for busmission
     *
     * Return null if sections are not ready or closing accounts are not added yet
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("double")
     * @JMS\SerializedName("totals_offset")
     */
    public function getTotalsOffset()
    {
        if ($this->getCalculatedBalance() === null || $this->getAccountsClosingBalanceTotal() === null) {
            return null;
        }

        return $this->getCalculatedBalance() - $this->getAccountsClosingBalanceTotal();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"balance"})
     * @JMS\Type("boolean")
     * @JMS\SerializedName("totals_match")
     */
    public function getTotalsMatch()
    {
        return $this->getTotalsOffset() !== null && abs($this->getTotalsOffset()) < 0.2;
    }
}

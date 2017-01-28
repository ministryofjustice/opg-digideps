<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;

class ReportStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /** @var Report */
    private $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /** @return string */
    public function getDecisionsState()
    {
        // no decisions, no tick, no mental capacity => grey
        if (empty($this->report->getDecisions()) && empty($this->report->getReasonForNoDecisions()) && empty($this->report->getMentalCapacity())) {
            return self::STATE_NOT_STARTED;
        }

        if ($this->missingDecisions()) {
            return self::STATE_INCOMPLETE;
        } else {
            return self::STATE_DONE;
        }
    }

    /** @return string */
    public function getContactsState()
    {
        if (empty($this->report->getContacts()) && empty($this->report->getReasonForNoContacts())) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }

    /** @return string */
    public function getVisitsCareState()
    {
        if (!$this->report->getVisitsCare()) {
            return self::STATE_NOT_STARTED;
        }
        if ($this->report->getVisitsCare()->missingInfo()) {
            return self::STATE_INCOMPLETE;
        }
        return self::STATE_DONE;
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->report->getBankAccounts())) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    public function getMoneyTransferState()
    {
        $hasAtLeastOneTransfer = count($this->report->getMoneyTransfers()) >= 1;
        $valid = $hasAtLeastOneTransfer || $this->report->getNoTransfersToAdd();

        if ($valid || count($this->report->getBankAccounts()) <= 1) {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    public function getMoneyInState()
    {
        if ($this->report->hasMoneyIn()) {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    public function getMoneyOutState()
    {
        if ($this->report->hasMoneyOut()) {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    public function getBalanceState()
    {
        if ($this->report->isMissingMoneyOrAccountsOrClosingBalance()) {
            return self::STATE_INCOMPLETE;
        }

        if ($this->report->isTotalsMatch()) {
            return self::STATE_DONE; // balance matching => complete
        }

        if ($this->report->getBalanceMismatchExplanation()) {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->report->getAssets()) > 0;
        $noAssetsToAdd = $this->report->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return self::STATE_NOT_STARTED;
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getDebtsState()
    {
        $hasDebts = $this->report->getHasDebts();

        if (in_array($hasDebts, ['yes', 'no'])) {
            return self::STATE_DONE;
        } else {
            return self::STATE_NOT_STARTED;
        }
    }

    /** @return string */
    public function getActionsState()
    {
        $action = $this->report->getAction();
        if (empty($action)) {
            return self::STATE_NOT_STARTED;
        }

        if ($action->getDoYouHaveConcerns() && $action->getDoYouExpectFinancialDecisions()) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return bool */
    public function hasOutstandingAccounts()
    {
        foreach ($this->report->getBankAccounts() as $account) {
            if (!$account->hasClosingBalance() || $account->hasMissingInformation()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getOtherInfoState()
    {
        if ($this->report->getActionMoreInfo() === null) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    /** @return bool */
    private function missingDecisions()
    {
        if (empty($this->report->getDecisions()) && !$this->report->getReasonForNoDecisions()) {
            return true;
        }

        if (empty($this->report->getMentalCapacity())) {
            return true;
        }

        return false;
    }

    /** @return bool */
    public function balanceMatches()
    {
        if ($this->report->getType() == Report::TYPE_103) {
            return true;
        }

        return $this->report->isTotalsMatch() || $this->report->getBalanceMismatchExplanation();
    }

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        $states = [
            'decisions' => $this->getDecisionsState(),
            'contacts' => $this->getContactsState(),
            'visitsCare' => $this->getVisitsCareState(),
            'actions' => $this->getActionsState(),
            'otherInfo' => $this->getOtherInfoState(),
            'gifts' => $this->getGiftsState(),
        ];

        $type = $this->report->getType();
        if ($type == Report::TYPE_102 || $type ==  Report::TYPE_103) {
            $states += [
                'bankAccounts' => $this->getBankAccountsState(),
                'deputyExpense' => $this->getExpensesState(),
                'moneyIn' => $this->getMoneyInState(),
                'moneyOut' => $this->getMoneyOutState(),
                'assets' => $this->getAssetsState(),
                'debts' => $this->getDebtsState(),
            ];

            if ($type == Report::TYPE_102) {
                $states += [
                    'moneyTransfers' => $this->getMoneyTransferState(),
                ];
            }
        }

        return array_filter($states, function ($e) {
            return $e != self::STATE_DONE;
        });
    }

    /**
     * @return string
     */
    public function getExpensesState()
    {
        if (count($this->report->getExpenses()) > 0 || $this->report->getPaidForAnything() === 'no') {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    /**
     * @return string
     */
    public function getGiftsState()
    {
        if (count($this->report->getGifts()) > 0 || $this->report->getGiftsExist() === 'no') {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0 && $this->balanceMatches();
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if ($this->isReadyToSubmit() && $this->report->isDue() && $this->balanceMatches()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}

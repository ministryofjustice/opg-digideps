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

    public function getMentalCapacityState()
    {
        if ($this->report->getMentalCapacity() &&
            $this->report->getMentalCapacity()->getHasCapacityChanged()
        ) {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
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
        $vc = $this->report->getVisitsCare();
        if (!$vc) {
            return self::STATE_NOT_STARTED;
        }

        if ($vc->getDoYouLiveWithClient()
            && $vc->getDoesClientReceivePaidCare()
            && $vc->getWhoIsDoingTheCaring()
            && $vc->getDoesClientHaveACarePlan()
        ) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->report->getAccounts())) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    public function getMoneyTransferState()
    {
        if (count($this->report->getAccounts()) <= 1) {
            return self::STATE_DONE;
        }

        $hasAtLeastOneTransfer = count($this->report->getMoneyTransfers()) >= 1;
        $valid = $hasAtLeastOneTransfer || $this->report->getNoTransfersToAdd();

        return $valid ? self::STATE_DONE : self::STATE_NOT_STARTED;
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

        return self::STATE_INCOMPLETE;
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

        if (empty($hasDebts)) {
            return self::STATE_NOT_STARTED;
        }

        $debtsSectionComplete = in_array($hasDebts, ['yes', 'no']);
        if ($debtsSectionComplete) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getActionsState()
    {
        return $this->report->getAction() ? self::STATE_DONE : self::STATE_NOT_STARTED;
    }

    /** @return bool */
    public function hasOutstandingAccounts()
    {
        foreach ($this->report->getAccounts() as $account) {
            if (!$account->hasClosingBalance() || $account->hasMissingInformation()) {
                return true;
            }
        }

        return false;
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
    private function missingBalance()
    {
        $balanceValid = $this->report->isTotalsMatch() || $this->report->getBalanceMismatchExplanation();

        return !$balanceValid;
    }

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        $states = [
            'decisions' => $this->getDecisionsState(),
            'mentalCapacity' => $this->getMentalCapacityState(),
            'contacts' => $this->getContactsState(),
            'visitsCare' => $this->getVisitsCareState(),
            'actions' => $this->getActionsState(),
        ];

        if ($this->report->getCourtOrderTypeId() == Report::PROPERTY_AND_AFFAIRS) {
            $states += [
                'bankAccounts' => $this->getBankAccountsState(),
                'moneyTransfers' => $this->getMoneyTransferState(),
                'moneyIn' => $this->getMoneyInState(),
                'moneyOut' => $this->getMoneyOutState(),
                'assets' => $this->getAssetsState(),
                'debts' => $this->getDebtsState(),
            ];
        }

        return array_filter($states, function ($e) {
            return $e != self::STATE_DONE;
        });
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0;
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if ($this->isReadyToSubmit() && $this->report->isDue()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}

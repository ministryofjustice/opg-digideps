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
        if (!$this->report->getVisitsCare() || $this->report->getVisitsCare()->missingVisitsCareInfo()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->report->getAccounts())) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    /** @return string */
    public function getAccountsState()
    {
        $missingAccounts = empty($this->report->getAccounts());

        // not started
        if ($missingAccounts && !$this->report->hasMoneyIn() && !$this->report->hasMoneyOut()) {
            return self::STATE_NOT_STARTED;
        }

        // all done
        if (!$missingAccounts && !$this->hasOutstandingAccounts() && $this->report->hasMoneyIn() && $this->report->hasMoneyOut() && !$this->missingTransfers() && !$this->missingBalance()) {
            return self::STATE_DONE;
        }

        // amber in all the other cases
        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->report->getAssets()) > 0;
        $noAssetsToAdd = $this->report->getNoAssetToAdd();
        $hasDebts = $this->report->getHasDebts();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd && empty($hasDebts)) {
            return self::STATE_NOT_STARTED;
        }

        $assetsSubSectionComplete = $hasAtLeastOneAsset || $noAssetsToAdd;
        $debtsSectionComplete = in_array($hasDebts, ['yes', 'no']);

        if ($assetsSubSectionComplete && $debtsSectionComplete) {
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

    /**
     * @return bool
     */
    private function missingTransfers()
    {
        if (count($this->report->getAccounts()) <= 1) {
            return false;
        }

        $hasAtLeastOneTransfer = count($this->report->getMoneyTransfers()) >= 1;
        $valid = $hasAtLeastOneTransfer || $this->report->getNoTransfersToAdd();

        return !$valid;
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
            'contacts' => $this->getContactsState(),
            'visitsCare' => $this->getVisitsCareState(),
            'actions' => $this->getActionsState(),
        ];

        if ($this->report->getCourtOrderTypeId() == Report::PROPERTY_AND_AFFAIRS) {
            $states += [
                'bankAccounts' => $this->getBankAccountsState(),
                'accounts' => $this->getAccountsState(),
                'assets' => $this->getAssetsState(),
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

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
        $decisionsValid = $this->report->getDecisions() || $this->report->getReasonForNoDecisions();

        if (!$this->report->getDecisions() && !$this->report->getReasonForNoDecisions() && !$this->report->getMentalCapacity()) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($decisionsValid && $this->report->getMentalCapacity() &&
            $this->report->getMentalCapacity()->getHasCapacityChanged()
            && $this->report->getMentalCapacity()->getMentalAssessmentDate()
        ) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getDecisions())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getContactsState()
    {
        if (empty($this->report->getContacts()) && empty($this->report->getReasonForNoContacts())) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        } else {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getContacts())];
        }
    }

    /** @return string */
    public function getVisitsCareState()
    {
        if (!$this->report->getVisitsCare()) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }
        if ($this->report->getVisitsCare()->missingInfo()) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getBankAccountsState()
    {
        $bankAccounts = $this->report->getBankAccounts();
        if (empty($bankAccounts)) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($this->report->getBankAccountsIncomplete()) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => count($bankAccounts)];
    }

    public function getMoneyTransferState()
    {
        $hasAtLeastOneTransfer = count($this->report->getMoneyTransfers()) >= 1;
        $valid = $hasAtLeastOneTransfer || $this->report->getNoTransfersToAdd();

        if ($valid || count($this->report->getBankAccounts()) <= 1) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransfers())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    public function getMoneyInState()
    {
        if ($this->report->hasMoneyIn()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsIn())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    public function getMoneyOutState()
    {
        if ($this->report->hasMoneyOut()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsOut())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    public function getMoneyInShortState()
    {
        $categoriesCount = count($this->report->getMoneyShortCategoriesInPresent());
        $exist = in_array($this->report->getMoneyTransactionsShortInExist(), ['yes', 'no']);

        if ($exist) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsShortIn())];
        }

        if ($categoriesCount) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    public function getMoneyOutShortState()
    {
        $categoriesCount = count($this->report->getMoneyShortCategoriesOutPresent());
        $exist = in_array($this->report->getMoneyTransactionsShortOutExist(), ['yes', 'no']);

        if ($exist) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getMoneyTransactionsShortOut())];
        }

        if ($categoriesCount) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    public function getBalanceState()
    {
        if ($this->report->isMissingMoneyOrAccountsOrClosingBalance()) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        if ($this->report->isTotalsMatch()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0]; // balance matching => complete
        }

        if ($this->report->getBalanceMismatchExplanation()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->report->getAssets()) > 0;
        $noAssetsToAdd = $this->report->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getAssets())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getDebtsState()
    {
        $hasDebts = $this->report->getHasDebts();

        if (in_array($hasDebts, ['yes', 'no'])) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getDebtsWithValidAmount())];
        } else {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }
    }

    /** @return string */
    public function getActionsState()
    {
        $action = $this->report->getAction();
        if (empty($action)) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($action->getDoYouHaveConcerns() && $action->getDoYouExpectFinancialDecisions()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /**
     * @return string
     */
    public function getOtherInfoState()
    {
        if ($this->report->getActionMoreInfo() === null) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
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
        return array_filter($this->getSectionStatus(), function ($e) {
            return $e != self::STATE_DONE;
        });
    }

    /**
     * @return array of section=>state
     */
    private function getSectionStatus()
    {
        $states = [
            'decisions'  => $this->getDecisionsState()['state'],
            'contacts'   => $this->getContactsState()['state'],
            'visitsCare' => $this->getVisitsCareState()['state'],
            'actions'    => $this->getActionsState()['state'],
            'otherInfo'  => $this->getOtherInfoState()['state'],
            'gifts'      => $this->getGiftsState()['state'],
        ];

        $type = $this->report->getType();


        if ($type == Report::TYPE_102) {
            $states += [
                'bankAccounts'  => $this->getBankAccountsState()['state'],
                'deputyExpense' => $this->getExpensesState()['state'],
                'moneyIn'       => $this->getMoneyInState()['state'],
                'moneyOut'      => $this->getMoneyOutState()['state'],
                'assets'        => $this->getAssetsState()['state'],
                'debts'         => $this->getDebtsState()['state'],
            ];

            if (count($this->report->getBankAccounts())) {
                $states += [
                    'moneyTransfers' => $this->getMoneyTransferState()['state'],
                ];
            }
        }

        if ($type == Report::TYPE_103) {
            $states += [
                'bankAccounts'  => $this->getBankAccountsState()['state'],
                'deputyExpense' => $this->getExpensesState()['state'],
                'moneyInShort'  => $this->getMoneyInShortState()['state'],
                'moneyOutShort' => $this->getMoneyOutShortState()['state'],
                'assets'        => $this->getAssetsState()['state'],
                'debts'         => $this->getDebtsState()['state'],
            ];
        }

        return $states;
    }

    /**
     * @return string
     */
    public function getExpensesState()
    {
        if (count($this->report->getExpenses()) > 0 || $this->report->getPaidForAnything() === 'no') {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getExpenses())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @return string
     */
    public function getGiftsState()
    {
        if (count($this->report->getGifts()) > 0 || $this->report->getGiftsExist() === 'no') {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->report->getGifts())];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0 && $this->balanceMatches();
    }

    /**
     * @return string $status | null
     */
    public function getSubmitState()
    {
        return [
            'state'      => $this->isReadyToSubmit() && $this->report->isDue() ? self::STATE_DONE : self::STATE_NOT_STARTED,
            'nOfRecords' => 0,
        ];
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if (count(array_filter($this->getSectionStatus(), function ($e) {
            return $e != self::STATE_NOT_STARTED;
        })) === 0
        ) {
            return 'notStarted';
        }

        if ($this->isReadyToSubmit() && $this->report->isDue() && $this->balanceMatches()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}

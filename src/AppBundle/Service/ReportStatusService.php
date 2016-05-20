<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report;
use Symfony\Component\Translation\TranslatorInterface;

class ReportStatusService
{

    const NOTSTARTED = 'not-started'; //grey
    const DONE = 'done'; //green
    const INCOMPLETE = 'incomplete'; //orange
    const NOTFINISHED = 'notFinished';
    const READYTOSUBMIT = 'readyToSubmit';
    
    const CLASS_NOT_STARTED = 'not-started';
    const CLASS_INCOMPLETE = 'incomplete';
    const CLASS_DONE = 'done';

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
            return self::CLASS_NOT_STARTED;
        }

        if ($this->missingDecisions()) {
            return self::CLASS_INCOMPLETE;
        } else {
            return self::CLASS_DONE;
        }
    }

    /** @return string */
    public function getDecisionsStatus()
    {
        switch ($this->getDecisionsState()) {
            case self::CLASS_NOT_STARTED:
                return 'notstarted';
            case self::CLASS_DONE:
                return 'finished';
            default:
                return 'notFinished';
        }
    }

    /** @return string */
    public function getContactsState()
    {
        if ($this->missingContacts()) {
            return self::NOTSTARTED;
        } else {
            return self::DONE;
        }
    }

    /** @return string */
    public function getContactsStatus()
    {
        $contacts = $this->report->getContacts();

        if (isset($contacts)) {
            $count = count($contacts);

            if ($count == 1) {
                return '1 Contact';
            } elseif ($count > 1) {
                return "$count Contacts";
            }
        }

        if (empty($this->report->getReasonForNoContacts())) {
            return 'notstarted';
        } else {
            return 'nocontacts';
        }
    }

    /** @return string */
    public function getSafeguardingStatus()
    {
        if ($this->missingSafeguarding()) {
            return 'notstarted';
        } else {
            return 'finished';
        }
    }

    /** @return string */
    public function getAssetsStatus()
    {
        $assets = $this->report->getAssets();

        if (isset($assets)) {
            $count = count($assets);

            if ($count == 1) {
                return '1 Asset';
            } elseif ($count > 1) {
                return "$count Assets";
            }
        }

        if ($this->report->getNoAssetToAdd() == true) {
            return 'noassets';
        } else {
            return 'notstarted';
        }
    }

    /** @return string */
    public function getAssetsState()
    {
        if ($this->missingAssets()) {
            return self::NOTSTARTED;
        } else {
            return self::DONE;
        }
    }

    /** @return string */
    public function getActionsStatus()
    {
        if ($this->missingActions()) {
            return 'notstarted';
        } else {
            return 'finished';
        }
    }

    /** @return string */
    public function getActionsState()
    {
        return $this->missingActions() ? self::NOTSTARTED : self::DONE;
    }

    /** @return string */
    public function getSafeguardingState()
    {
        if ($this->missingSafeguarding()) {
            return self::NOTSTARTED;
        } else {
            return self::DONE;
        }
    }

    /** @return string */
    public function getAccountsState()
    {
        // not started
        if ($this->missingAccounts() && !$this->report->hasMoneyIn() && !$this->report->hasMoneyOut()) {
            return self::CLASS_NOT_STARTED;
        }

        // all done
        if (!$this->missingAccounts() && !$this->hasOutstandingAccounts() && $this->report->hasMoneyIn() && $this->report->hasMoneyOut() && !$this->missingTransfers() && !$this->missingBalance()) {
            return self::DONE;
        }

        // amber in all the other cases
        return self::CLASS_INCOMPLETE;
    }

    /** @return string */
    public function getAccountsStatus()
    {
        switch ($this->getAccountsState()) {
            case self::CLASS_NOT_STARTED:
                return 'notstarted';
            case self::CLASS_DONE:
                return 'finished';
            default:
                return 'notFinished';
        }
    }

    /** @return bool */
    private function missingAssets()
    {
        return empty($this->report->getAssets()) && (!$this->report->getNoAssetToAdd());
    }

    /** @return bool */
    private function missingSafeguarding()
    {
        $safeguarding = $this->report->getSafeguarding();
        
        return !$safeguarding || $safeguarding->missingSafeguardingInfo() == true;
    }

    /** @return bool */
    private function missingActions()
    {
        return !$this->report->getAction();
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
    private function missingContacts()
    {
        return empty($this->report->getContacts()) && empty($this->report->getReasonForNoContacts());
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
    private function missingAccounts()
    {
        return empty($this->report->getAccounts());
    }

    /**
     * If.
     *
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
        $ret = [];

        $isPropAndAff = $this->report->getCourtOrderTypeId() == Report::PROPERTY_AND_AFFAIRS;
        
        if ($this->missingDecisions()) {
            $ret[] = 'decisions';
        }
        
        if ($this->getContactsStatus() == 'not-started') {
            $ret[] = 'contacts';
        }
        
        if ($this->missingSafeguarding()) {
            $ret[] = 'safeguarding';
        }
        
        if ($isPropAndAff && ($this->hasOutstandingAccounts() || $this->missingAccounts() 
                || $this->missingTransfers() || $this->missingBalance())) {
            $ret[] = 'account';
        }

        if ($isPropAndAff && $this->missingAssets()) {
            $ret[] = 'assets';
        }

        if ($this->missingActions()) {
            $ret[] = 'actions';
        }

        return $ret;
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
        $readyToSubmit = $this->isReadyToSubmit();

        if ($readyToSubmit && $this->report->isDue()) {
            return 'readyToSubmit';
        } else {
            return self::NOTFINISHED;
        }
    }
}

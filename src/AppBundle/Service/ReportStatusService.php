<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report;
use Symfony\Component\Translation\TranslatorInterface;

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
        if ($this->missingContacts()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }

    /** @return string */
    public function getSafeguardingState()
    {
        if ($this->missingSafeguarding()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }
    
    /** @return string */
    public function getAccountsState()
    {
        // not started
        if ($this->missingAccounts() && !$this->report->hasMoneyIn() && !$this->report->hasMoneyOut()) {
            return self::STATE_NOT_STARTED;
        }

        // all done
        if (!$this->missingAccounts() && !$this->hasOutstandingAccounts() && $this->report->hasMoneyIn() && $this->report->hasMoneyOut() && !$this->missingTransfers() && !$this->missingBalance()) {
            return self::STATE_DONE;
        }

        // amber in all the other cases
        return self::STATE_INCOMPLETE;
    }
    
    
    /** @return string */
    public function getAssetsState()
    {
        if ($this->missingAssets()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }
    
    /** @return string */
    public function getActionsState()
    {
        return $this->missingActions() ? self::STATE_NOT_STARTED : self::STATE_DONE;
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
        $states = [
            'decisions' => $this->getDecisionsState(),
            'contacts' => $this->getContactsState(),
            'safeguarding' => $this->getSafeguardingState(),
            'actions' => $this->getActionsState(),
        ];
        
        if ($this->report->getCourtOrderTypeId() == Report::PROPERTY_AND_AFFAIRS) {
            $states += [
                'accounts' => $this->getAccountsState(),
                'assets' => $this->getAssetsState(),
            ];
        }
        
        return array_filter($states, function($e) {
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

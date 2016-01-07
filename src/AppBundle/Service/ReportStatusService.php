<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report;
use AppBundle\Entity\Account;
use Symfony\Component\Translation\TranslatorInterface;

class ReportStatusService
{

    const NOTSTARTED = "not-started";
    const DONE = "done";
    const INCOMPLETE = "incomplete";
    const NOTFINISHED = "notFinished";
    const READYTOSUBMIT = "readyToSubmit";


    /** @var Report */
    private $report;

    /** @var TranslatorInterface */
    private $translator;


    public function __construct(Report $report, TranslatorInterface $translator)
    {
        $this->report = $report;
        $this->translator = $translator;
    }


    /** @return string */
    public function getDecisionsStatus()
    {

        $decisions = $this->report->getDecisions();

        if (isset($decisions)) {

            $count = count($decisions);

            if ($count == 1) {
                return "1 " . $this->translator->trans('decision', [], 'status');
            } else if ($count > 1) {
                return "${count} " . $this->translator->trans('decisions', [], 'status');
            }
        }

        if (empty($this->report->getReasonForNoDecisions())) {
            return $this->translator->trans('notstarted', [], 'status');
        } else {
            return $this->translator->trans('nodecisions', [], 'status');
        }
    }


    /** @return string */
    public function getContactsStatus()
    {

        $contacts = $this->report->getContacts();

        if (isset($contacts)) {

            $count = count($contacts);
            
            // TODO use transcount
            if ($count == 1) {
                return "1 " . $this->translator->trans('contact', [], 'status');
            } else if ($count > 1) {
                return "${count} " . $this->translator->trans('contacts', [], 'status');
            }
        }

        if (empty($this->report->getReasonForNoContacts())) {
            return $this->translator->trans('notstarted', [], 'status');
        } else {
            return $this->translator->trans('nocontacts', [], 'status');
        }
    }


    /** @return string */
    public function getSafeguardingStatus()
    {
        if ($this->missingSafeguarding()) {
            return $this->translator->trans('notstarted', [], 'status');
        } else {
            return $this->translator->trans('finished', [], 'status');
        }
    }


    /** @return string */
    public function getAccountsStatus()
    {
        if ($this->missingAccounts()) {
            return $this->translator->trans('notstarted', [], 'status');
        }

        $count = count($this->report->getAccounts());
        if ($count === 1) {
            return "1 " . $this->translator->trans("account", [], 'status');
        } else {
            return "${count} " . $this->translator->trans("accounts", [], 'status');
        }
    }


    /** @return string */
    public function getAssetsStatus()
    {
        $assets = $this->report->getAssets();

        if (isset($assets)) {

            $count = count($assets);

            if ($count == 1) {
                return "1 " . $this->translator->trans('asset', [], 'status');
            } else if ($count > 1) {
                return "${count} " . $this->translator->trans('assets', [], 'status');
            }
        }

        if ($this->report->getNoAssetToAdd() == true) {
            return $this->translator->trans('noassets', [], 'status');
        } else {
            return $this->translator->trans('notstarted', [], 'status');
        }
    }


    /** @return string */
    public function getDecisionsState()
    {
        if ($this->missingDecisions()) {
            return $this::NOTSTARTED;
        } else {
            return $this::DONE;
        }
    }


    /** @return string */
    public function getContactsState()
    {
        if ($this->missingContacts()) {
            return $this::NOTSTARTED;
        } else {
            return $this::DONE;
        }
    }


    /** @return string */
    public function getSafeguardingState()
    {
        if ($this->missingSafeguarding()) {
            return $this::NOTSTARTED;
        } else {
            return $this::DONE;
        }
    }


    /** @return string */
    public function getAccountsState()
    {
        if ($this->missingAccounts()) {
            return $this::NOTSTARTED;
        } else if ($this->hasOutstandingAccounts() || $this->missingBalance()) {
            return $this::INCOMPLETE;
        } else {
            return $this::DONE;
        }
    }


    /** @return string */
    public function getAssetsState()
    {
        if ($this->missingAssets()) {
            return $this::NOTSTARTED;
        } else {
            return $this::DONE;
        }
    }


    /** @return boolean */
    public function isReadyToSubmit()
    {
        if ($this->report->getCourtOrderType() == Report::PROPERTY_AND_AFFAIRS) {
            return !$this->hasOutstandingAccounts() 
                && !$this->missingAccounts() 
                && !$this->missingBalance()
                && !$this->missingContacts() 
                && !$this->missingAssets() 
                && !$this->missingDecisions() 
                && !$this->missingSafeguarding();
        } else {
            return !$this->missingContacts() 
                && !$this->missingDecisions() 
                && !$this->missingSafeguarding();
        }
    }


    /** @return boolean */
    public function missingAssets()
    {
        return (empty($this->report->getAssets()) && (!$this->report->getNoAssetToAdd()));
    }


    /** @return boolean */
    public function missingSafeguarding()
    {
        $safeguarding = $this->report->getSafeguarding();

        return (!$safeguarding || $safeguarding->missingSafeguardingInfo() == true);
    }


    /** @return boolean */
    public function hasOutstandingAccounts()
    {
        foreach ($this->report->getAccounts() as $account) {
            if (!$account->hasClosingBalance()) {
                return true;
            }
        }

        return false;
    }


    /** @return boolean */
    public function missingContacts()
    {
        return (empty($this->report->getContacts()) && empty($this->report->getReasonForNoContacts()));
    }


    /** @return boolean */
    public function missingDecisions()
    {
        return (empty($this->report->getDecisions()) && empty($this->report->getReasonForNoDecisions()));
    }


    /** @return boolean */
    public function missingAccounts()
    {
        return empty($this->report->getAccounts());
    }
    
    /** @return boolean */
    public function missingBalance()
    {
        $balanceValid = $this->report->isTotalsMatch() || $this->report->getBalanceMismatchExplanation();
        
        return !$balanceValid;
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        $readyToSubmit = $this->isReadyToSubmit();

        if ($readyToSubmit && $this->report->isDue()) {
            return $this::READYTOSUBMIT;
        } else {
            return $this::NOTFINISHED;
        }
    }


    public function getRemainingSectionCount()
    {

        if ($this->report->getCourtOrderType() == Report::PROPERTY_AND_AFFAIRS) {
            $count = 5;

            if (!$this->hasOutstandingAccounts() && !$this->missingAccounts() && !$this->missingBalance()) {
                $count--;
            }

            if (!$this->missingContacts()) {
                $count--;
            }

            if (!$this->missingAssets()) {
                $count--;
            }

            if (!$this->missingDecisions()) {
                $count--;
            }

            if (!$this->missingSafeguarding()) {
                $count--;
            }
        } else {
            $count = 3;

            if (!$this->missingContacts()) {
                $count--;
            }

            if (!$this->missingSafeguarding()) {
                $count--;
            }

            if (!$this->missingDecisions()) {
                $count--;
            }
        }

        return $count;
    }

}
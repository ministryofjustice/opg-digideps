<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class Status
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /**
     * @var Report
     * @JMS\Type("AppBundle\Entity\Report\Report")
     */
    private $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $decisionsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $contactsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $visitsCareState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $bankAccountsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $moneyTransferState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $moneyInState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $moneyOutState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $moneyInShortState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $moneyOutShortState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $balanceState;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $balanceMatches;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $submitState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $assetsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $debtsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $paFeesExpensesState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $actionsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $otherInfoState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $remainingSections;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $sectionStatus;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $expensesState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $giftsState;

    /**
     * @JMS\Type("boolean")
     *
     * @var array
     */
    private $isReadyToSubmit;

    /**
     * @JMS\Type("string")
     *
     * @var array
     */
    private $status;

    /**
     * @return mixed
     */
    public function getDecisionsState()
    {
        return $this->decisionsState;
    }

    /**
     * @param mixed $decisionsState
     */
    public function setDecisionsState($decisionsState)
    {
        $this->decisionsState = $decisionsState;
    }

    /**
     * @return mixed
     */
    public function getContactsState()
    {
        return $this->contactsState;
    }

    /**
     * @param mixed $contactsState
     */
    public function setContactsState($contactsState)
    {
        $this->contactsState = $contactsState;
    }

    /**
     * @return mixed
     */
    public function getVisitsCareState()
    {
        return $this->visitsCareState;
    }

    /**
     * @param mixed $visitsCareState
     */
    public function setVisitsCareState($visitsCareState)
    {
        $this->visitsCareState = $visitsCareState;
    }

    /**
     * @return mixed
     */
    public function getBankAccountsState()
    {
        return $this->bankAccountsState;
    }

    /**
     * @param mixed $bankAccountsState
     */
    public function setBankAccountsState($bankAccountsState)
    {
        $this->bankAccountsState = $bankAccountsState;
    }

    /**
     * @return mixed
     */
    public function getMoneyTransferState()
    {
        return $this->moneyTransferState;
    }

    /**
     * @param mixed $moneyTransferState
     */
    public function setMoneyTransferState($moneyTransferState)
    {
        $this->moneyTransferState = $moneyTransferState;
    }

    /**
     * @return mixed
     */
    public function getMoneyInState()
    {
        return $this->moneyInState;
    }

    /**
     * @param mixed $moneyInState
     */
    public function setMoneyInState($moneyInState)
    {
        $this->moneyInState = $moneyInState;
    }

    /**
     * @return mixed
     */
    public function getMoneyOutState()
    {
        return $this->moneyOutState;
    }

    /**
     * @param mixed $moneyOutState
     */
    public function setMoneyOutState($moneyOutState)
    {
        $this->moneyOutState = $moneyOutState;
    }

    /**
     * @return mixed
     */
    public function getMoneyInShortState()
    {
        return $this->moneyInShortState;
    }

    /**
     * @param mixed $moneyInShortState
     */
    public function setMoneyInShortState($moneyInShortState)
    {
        $this->moneyInShortState = $moneyInShortState;
    }

    /**
     * @return mixed
     */
    public function getMoneyOutShortState()
    {
        return $this->moneyOutShortState;
    }

    /**
     * @param mixed $moneyOutShortState
     */
    public function setMoneyOutShortState($moneyOutShortState)
    {
        $this->moneyOutShortState = $moneyOutShortState;
    }

    /**
     * @return mixed
     */
    public function getBalanceState()
    {
        return $this->balanceState;
    }

    /**
     * @param mixed $balanceState
     */
    public function setBalanceState($balanceState)
    {
        $this->balanceState = $balanceState;
    }

    /**
     * @return mixed
     */
    public function getAssetsState()
    {
        return $this->assetsState;
    }

    /**
     * @param mixed $assetsState
     */
    public function setAssetsState($assetsState)
    {
        $this->assetsState = $assetsState;
    }

    /**
     * @return mixed
     */
    public function getDebtsState()
    {
        return $this->debtsState;
    }

    /**
     * @param mixed $debtsState
     */
    public function setDebtsState($debtsState)
    {
        $this->debtsState = $debtsState;
    }

    /**
     * @return array
     */
    public function getPaFeesExpensesState()
    {
        return $this->paFeesExpensesState;
    }

    /**
     * @param array $paFeesExpensesState
     */
    public function setPaFeesExpensesState($paFeesExpensesState)
    {
        $this->paFeesExpensesState = $paFeesExpensesState;
    }

    /**
     * @return mixed
     */
    public function getActionsState()
    {
        return $this->actionsState;
    }

    /**
     * @param mixed $actionsState
     */
    public function setActionsState($actionsState)
    {
        $this->actionsState = $actionsState;
    }

    /**
     * @return mixed
     */
    public function getOtherInfoState()
    {
        return $this->otherInfoState;
    }

    /**
     * @param mixed $otherInfoState
     */
    public function setOtherInfoState($otherInfoState)
    {
        $this->otherInfoState = $otherInfoState;
    }

    /**
     * @return mixed
     */
    public function getRemainingSections()
    {
        return $this->remainingSections;
    }

    /**
     * @param mixed $remainingSections
     */
    public function setRemainingSections($remainingSections)
    {
        $this->remainingSections = $remainingSections;
    }

    /**
     * @return mixed
     */
    public function getSectionStatus()
    {
        return $this->sectionStatus;
    }

    /**
     * @param mixed $sectionStatus
     */
    public function setSectionStatus($sectionStatus)
    {
        $this->sectionStatus = $sectionStatus;
    }

    /**
     * @return mixed
     */
    public function getExpensesState()
    {
        return $this->expensesState;
    }

    /**
     * @param mixed $expensesState
     */
    public function setExpensesState($expensesState)
    {
        $this->expensesState = $expensesState;
    }

    /**
     * @return mixed
     */
    public function getGiftsState()
    {
        return $this->giftsState;
    }

    /**
     * @param mixed $giftsState
     */
    public function setGiftsState($giftsState)
    {
        $this->giftsState = $giftsState;
    }

    /**
     * @return mixed
     */
    public function isReadyToSubmit()
    {
        return $this->isReadyToSubmit;
    }

    /**
     * //TODO remove and clean up if not used
     * @return mixed
     */
    public function getIsReadyToSubmit()
    {
        return $this->isReadyToSubmit;
    }

    /**
     * @param mixed $isReadyToSubmit
     */
    public function setIsReadyToSubmit($isReadyToSubmit)
    {
        $this->isReadyToSubmit = $isReadyToSubmit;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isBalanceMatches()
    {
        return $this->balanceMatches;
    }

    /**
     * @param  bool   $balanceMatches
     * @return Status
     */
    public function setBalanceMatches($balanceMatches)
    {
        $this->balanceMatches = $balanceMatches;

        return $this;
    }

    /**
     * @return array
     */
    public function getSubmitState()
    {
        return $this->submitState;
    }

    /**
     * @param  array  $submitState
     * @return Status
     */
    public function setSubmitState($submitState)
    {
        $this->submitState = $submitState;

        return $this;
    }
}

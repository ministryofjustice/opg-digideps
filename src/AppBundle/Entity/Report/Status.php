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
    private $expensesState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $giftsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $documentsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $lifestyleState;

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
     * @JMS\Type("array")
     *
     * @var array
     */
    private $profCurrentFeesState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $profDeputyCostsState;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $profDeputyCostsEstimateState;

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
     * @return array
     */
    public function getDocumentsState()
    {
        return $this->documentsState;
    }

    /**
     * @param array $documentsState
     */
    public function setDocumentsState($documentsState)
    {
        $this->documentsState = $documentsState;
    }

    /**
     * @return array
     */
    public function getLifestyleState()
    {
        return $this->lifestyleState;
    }

    /**
     * @param array $lifestyleState
     */
    public function setLifestyleState($lifestyleState)
    {
        $this->lifestyleState = $lifestyleState;
    }

    /**
     * @return array
     */
    public function getProfCurrentFeesState()
    {
        return $this->profCurrentFeesState;
    }

    /**
     * @param array $profCurrentFeesState
     */
    public function setProfCurrentFeesState($profCurrentFeesState)
    {
        $this->profCurrentFeesState = $profCurrentFeesState;
    }

    /**
     * @return array
     */
    public function getProfDeputyCostsState()
    {
        return $this->profDeputyCostsState;
    }

    /**
     * @param array $profDeputyCostsState
     */
    public function setProfDeputyCostsState($profDeputyCostsState)
    {
        $this->profDeputyCostsState = $profDeputyCostsState;
    }

    /**
     * @return array
     */
    public function getProfDeputyCostsEstimateState()
    {
        return $this->profDeputyCostsEstimateState;
    }

    /**
     * @param array $profDeputyCostsEstimateState
     */
    public function setProfDeputyCostsEstimateState($profDeputyCostsEstimateState)
    {
        $this->profDeputyCostsEstimateState = $profDeputyCostsEstimateState;
    }

    public function getState()
    {
        switch ($this->status) {
            case 'notStarted':
                return Status::STATE_NOT_STARTED;
            case 'notFinished':
                return Status::STATE_INCOMPLETE;
            case 'readyToSubmit':
                return Status::STATE_DONE;
        }
    }
}

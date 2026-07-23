<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class Status
{
    public const string STATE_NOT_STARTED = 'not-started';
    public const string STATE_INCOMPLETE = 'incomplete';
    public const string STATE_DONE = 'done';

    /**
     * @var Report
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $decisionsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $contactsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $visitsCareState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $clientBenefitsCheckState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $bankAccountsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $moneyTransferState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $moneyInState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $moneyOutState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $moneyInShortState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $moneyOutShortState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $balanceState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $assetsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $debtsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $paFeesExpensesState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $actionsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $otherInfoState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $expensesState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $giftsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $documentsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $lifestyleState = [];

    /**
     * @var array
     */
    #[JMS\Type('boolean')]
    private $isReadyToSubmit;

    /**
     * @var array
     */
    #[JMS\Type('string')]
    private $status;

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $profCurrentFeesState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $profDeputyCostsState = [];

    /**
     * @var array
     */
    #[JMS\Type('array')]
    private $profDeputyCostsEstimateState = [];

    /**
     * @return array
     */
    public function getDecisionsState(): array
    {
        return $this->decisionsState;
    }

    /**
     * @param mixed $decisionsState
     */
    public function setDecisionsState($decisionsState): void
    {
        $this->decisionsState = $decisionsState;
    }

    /**
     * @return array
     */
    public function getContactsState(): array
    {
        return $this->contactsState;
    }

    /**
     * @param mixed $contactsState
     */
    public function setContactsState($contactsState): void
    {
        $this->contactsState = $contactsState;
    }

    /**
     * @return array
     */
    public function getVisitsCareState(): array
    {
        return $this->visitsCareState;
    }

    /**
     * @param mixed $visitsCareState
     */
    public function setVisitsCareState($visitsCareState): void
    {
        $this->visitsCareState = $visitsCareState;
    }

    /**
     * @return array
     */
    public function getBankAccountsState(): array
    {
        return $this->bankAccountsState;
    }

    /**
     * @param mixed $bankAccountsState
     */
    public function setBankAccountsState($bankAccountsState): void
    {
        $this->bankAccountsState = $bankAccountsState;
    }

    /**
     * @return array
     */
    public function getMoneyTransferState(): array
    {
        return $this->moneyTransferState;
    }

    /**
     * @param mixed $moneyTransferState
     */
    public function setMoneyTransferState($moneyTransferState): void
    {
        $this->moneyTransferState = $moneyTransferState;
    }

    /**
     * @return array
     */
    public function getMoneyInState(): array
    {
        return $this->moneyInState;
    }

    /**
     * @param mixed $moneyInState
     */
    public function setMoneyInState($moneyInState): void
    {
        $this->moneyInState = $moneyInState;
    }

    /**
     * @return array
     */
    public function getMoneyOutState(): array
    {
        return $this->moneyOutState;
    }

    /**
     * @param mixed $moneyOutState
     */
    public function setMoneyOutState($moneyOutState): void
    {
        $this->moneyOutState = $moneyOutState;
    }

    /**
     * @return array
     */
    public function getMoneyInShortState(): array
    {
        return $this->moneyInShortState;
    }

    /**
     * @param mixed $moneyInShortState
     */
    public function setMoneyInShortState($moneyInShortState): void
    {
        $this->moneyInShortState = $moneyInShortState;
    }

    /**
     * @return array
     */
    public function getMoneyOutShortState(): array
    {
        return $this->moneyOutShortState;
    }

    /**
     * @param mixed $moneyOutShortState
     */
    public function setMoneyOutShortState($moneyOutShortState): void
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
    public function setBalanceState($balanceState): void
    {
        $this->balanceState = $balanceState;
    }

    /**
     * @return array
     */
    public function getAssetsState(): array
    {
        return $this->assetsState;
    }

    /**
     * @param mixed $assetsState
     */
    public function setAssetsState($assetsState): void
    {
        $this->assetsState = $assetsState;
    }

    /**
     * @return array
     */
    public function getDebtsState(): array
    {
        return $this->debtsState;
    }

    /**
     * @param mixed $debtsState
     */
    public function setDebtsState($debtsState): void
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
    public function setPaFeesExpensesState($paFeesExpensesState): void
    {
        $this->paFeesExpensesState = $paFeesExpensesState;
    }

    /**
     * @return array
     */
    public function getActionsState(): array
    {
        return $this->actionsState;
    }

    /**
     * @param mixed $actionsState
     */
    public function setActionsState($actionsState): void
    {
        $this->actionsState = $actionsState;
    }

    /**
     * @return array
     */
    public function getOtherInfoState(): array
    {
        return $this->otherInfoState;
    }

    /**
     * @param mixed $otherInfoState
     */
    public function setOtherInfoState($otherInfoState): void
    {
        $this->otherInfoState = $otherInfoState;
    }

    /**
     * @return array
     */
    public function getExpensesState(): array
    {
        return $this->expensesState;
    }

    /**
     * @param mixed $expensesState
     */
    public function setExpensesState($expensesState): void
    {
        $this->expensesState = $expensesState;
    }

    /**
     * @return array
     */
    public function getGiftsState(): array
    {
        return $this->giftsState;
    }

    /**
     * @param mixed $giftsState
     */
    public function setGiftsState($giftsState): void
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
    public function setIsReadyToSubmit($isReadyToSubmit): void
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
    public function setStatus($status): void
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
    public function setDocumentsState($documentsState): void
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
    public function setLifestyleState($lifestyleState): void
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
    public function setProfCurrentFeesState($profCurrentFeesState): void
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
    public function setProfDeputyCostsState($profDeputyCostsState): void
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
    public function setProfDeputyCostsEstimateState($profDeputyCostsEstimateState): void
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

    public function getClientBenefitsCheckState(): array
    {
        return $this->clientBenefitsCheckState;
    }

    public function setClientBenefitsCheckState(array $clientBenefitsCheckState): Status
    {
        $this->clientBenefitsCheckState = $clientBenefitsCheckState;

        return $this;
    }
}

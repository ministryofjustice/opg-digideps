<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class Status
{
    public const string STATE_NOT_STARTED = 'not-started';
    public const string STATE_INCOMPLETE = 'incomplete';
    public const string STATE_DONE = 'done';

    #[JMS\Type('OPG\Digideps\Frontend\Entity\Report\Report')]
    private Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    #[JMS\Type('array')]
    private array $decisionsState = [];

    #[JMS\Type('array')]
    private array $contactsState = [];

    #[JMS\Type('array')]
    private array $visitsCareState = [];

    #[JMS\Type('array')]
    private array $clientBenefitsCheckState = [];
    #[JMS\Type('array')]
    private array $bankAccountsState = [];

    #[JMS\Type('array')]
    private array $moneyTransferState = [];

    #[JMS\Type('array')]
    private array $moneyInState = [];

    #[JMS\Type('array')]
    private array $moneyOutState = [];

    #[JMS\Type('array')]
    private array $moneyInShortState = [];

    #[JMS\Type('array')]
    private array $moneyOutShortState = [];

    #[JMS\Type('array')]
    private array $balanceState = [];

    #[JMS\Type('array')]
    private array $assetsState = [];

    #[JMS\Type('array')]
    private array $debtsState = [];

    #[JMS\Type('array')]
    private array $paFeesExpensesState = [];

    #[JMS\Type('array')]
    private array $actionsState = [];

    #[JMS\Type('array')]
    private array $otherInfoState = [];

    #[JMS\Type('array')]
    private array $expensesState = [];

    #[JMS\Type('array')]
    private array $giftsState = [];

    #[JMS\Type('array')]
    private array $documentsState = [];

    #[JMS\Type('array')]
    private array $lifestyleState = [];

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

    #[JMS\Type('array')]
    private array $profCurrentFeesState = [];

    #[JMS\Type('array')]
    private array $profDeputyCostsState = [];

    #[JMS\Type('array')]
    private array $profDeputyCostsEstimateState = [];
    public function getDecisionsState(): array
    {
        return $this->decisionsState;
    }

    public function setDecisionsState(array $decisionsState): static
    {
        $this->decisionsState = $decisionsState;

        return $this;
    }

    public function getContactsState(): array
    {
        return $this->contactsState;
    }

    public function setContactsState(array $contactsState): static
    {
        $this->contactsState = $contactsState;

        return $this;
    }

    public function getVisitsCareState(): array
    {
        return $this->visitsCareState;
    }

    public function setVisitsCareState(array $visitsCareState): static
    {
        $this->visitsCareState = $visitsCareState;

        return $this;
    }

    public function getBankAccountsState(): array
    {
        return $this->bankAccountsState;
    }

    public function setBankAccountsState(array $bankAccountsState): static
    {
        $this->bankAccountsState = $bankAccountsState;

        return $this;
    }

    public function getMoneyTransferState(): array
    {
        return $this->moneyTransferState;
    }

    public function setMoneyTransferState(array $moneyTransferState): static
    {
        $this->moneyTransferState = $moneyTransferState;

        return $this;
    }

    public function getMoneyInState(): array
    {
        return $this->moneyInState;
    }

    public function setMoneyInState(array $moneyInState): static
    {
        $this->moneyInState = $moneyInState;

        return $this;
    }

    public function getMoneyOutState(): array
    {
        return $this->moneyOutState;
    }

    public function setMoneyOutState(array $moneyOutState): static
    {
        $this->moneyOutState = $moneyOutState;

        return $this;
    }

    public function getMoneyInShortState(): array
    {
        return $this->moneyInShortState;
    }

    public function setMoneyInShortState($moneyInShortState): static
    {
        $this->moneyInShortState = $moneyInShortState;

        return $this;
    }

    public function getMoneyOutShortState(): array
    {
        return $this->moneyOutShortState;
    }

    public function setMoneyOutShortState(array $moneyOutShortState): static
    {
        $this->moneyOutShortState = $moneyOutShortState;

        return $this;
    }

    public function getBalanceState(): array
    {
        return $this->balanceState;
    }

    public function setBalanceState(array $balanceState): static
    {
        $this->balanceState = $balanceState;

        return $this;
    }

    public function getAssetsState(): array
    {
        return $this->assetsState;
    }

    public function setAssetsState(array $assetsState): static
    {
        $this->assetsState = $assetsState;

        return $this;
    }

    public function getDebtsState(): array
    {
        return $this->debtsState;
    }

    public function setDebtsState(array $debtsState): static
    {
        $this->debtsState = $debtsState;

        return $this;
    }

    public function getPaFeesExpensesState(): array
    {
        return $this->paFeesExpensesState;
    }

    public function setPaFeesExpensesState(array $paFeesExpensesState): static
    {
        $this->paFeesExpensesState = $paFeesExpensesState;

        return $this;
    }

    public function getActionsState(): array
    {
        return $this->actionsState;
    }

    public function setActionsState(array $actionsState): static
    {
        $this->actionsState = $actionsState;

        return $this;
    }

    public function getOtherInfoState(): array
    {
        return $this->otherInfoState;
    }

    /**
     * @param mixed $otherInfoState
     */
    public function setOtherInfoState($otherInfoState): static
    {
        $this->otherInfoState = $otherInfoState;

        return $this;
    }

    public function getExpensesState(): array
    {
        return $this->expensesState;
    }

    public function setExpensesState(array $expensesState): static
    {
        $this->expensesState = $expensesState;

        return $this;
    }

    public function getGiftsState(): array
    {
        return $this->giftsState;
    }

    public function setGiftsState(array $giftsState): static
    {
        $this->giftsState = $giftsState;

        return $this;
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

    public function setIsReadyToSubmit(array $isReadyToSubmit): static
    {
        $this->isReadyToSubmit = $isReadyToSubmit;

        return $this;
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
    public function setStatus($status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDocumentsState(): array
    {
        return $this->documentsState;
    }

    public function setDocumentsState(array $documentsState): static
    {
        $this->documentsState = $documentsState;

        return $this;
    }

    public function getLifestyleState(): array
    {
        return $this->lifestyleState;
    }

    public function setLifestyleState(array $lifestyleState): static
    {
        $this->lifestyleState = $lifestyleState;

        return $this;
    }

    public function getProfCurrentFeesState(): array
    {
        return $this->profCurrentFeesState;
    }

    public function setProfCurrentFeesState(array $profCurrentFeesState): static
    {
        $this->profCurrentFeesState = $profCurrentFeesState;

        return $this;
    }

    public function getProfDeputyCostsState(): array
    {
        return $this->profDeputyCostsState;
    }

    public function setProfDeputyCostsState(array $profDeputyCostsState): static
    {
        $this->profDeputyCostsState = $profDeputyCostsState;

        return $this;
    }

    public function getProfDeputyCostsEstimateState(): array
    {
        return $this->profDeputyCostsEstimateState;
    }

    public function setProfDeputyCostsEstimateState(array $profDeputyCostsEstimateState): static
    {
        $this->profDeputyCostsEstimateState = $profDeputyCostsEstimateState;

        return $this;
    }

    public function getState(): string
    {
        switch ($this->status) {
            case 'notFinished':
                return Status::STATE_INCOMPLETE;
            case 'readyToSubmit':
                return Status::STATE_DONE;
            case 'notStarted':
            default:
                return Status::STATE_NOT_STARTED;
        }
    }

    public function getClientBenefitsCheckState(): array
    {
        return $this->clientBenefitsCheckState;
    }

    public function setClientBenefitsCheckState(array $clientBenefitsCheckState): static
    {
        $this->clientBenefitsCheckState = $clientBenefitsCheckState;

        return $this;
    }
}

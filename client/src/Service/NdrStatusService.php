<?php

namespace App\Service;

use App\Entity\Ndr\Ndr;
use App\Entity\Ndr\VisitsCare;
use App\Entity\Report\ClientBenefitsCheck;

class NdrStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /** @var Ndr */
    private $ndr;

    public function __construct(Ndr $ndr)
    {
        $this->ndr = $ndr;
    }

    /**
     * @return array
     */
    private function getSectionStatus()
    {
        return [
            'visitsCare' => $this->getVisitsCareState()['state'],
            'expenses' => $this->getExpensesState()['state'],
            'incomeBenefits' => $this->getIncomeBenefitsState()['state'],
            'assets' => $this->getAssetsState()['state'],
            'bankAccounts' => $this->getBankAccountsState()['state'],
            'debts' => $this->getDebtsState()['state'],
            'actions' => $this->getActionsState()['state'],
            'otherInfo' => $this->getOtherInfoState()['state'],
            'clientBenefitsCheck' => $this->getClientBenefitsCheckState()['state'],
        ];
    }

    /** @return string */
    public function getVisitsCareState()
    {
        /** @var VisitsCare $visitsCare */
        $visitsCare = $this->ndr->getVisitsCare();
        $answers = [
            $visitsCare->getDoYouLiveWithClient(),
            $visitsCare->getDoesClientHaveACarePlan(),
            $visitsCare->getWhoIsDoingTheCaring(),
            $visitsCare->getDoesClientHaveACarePlan(),
            $visitsCare->getPlanMoveNewResidence(),
        ];

        switch (count(array_filter($answers))) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case 5:
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }

    /**
     * @return string
     */
    public function getExpensesState()
    {
        $nOfExpenses = count($this->ndr->getExpenses());
        if ($nOfExpenses > 0 || 'no' === $this->ndr->getPaidForAnything()) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => $nOfExpenses];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
    }

    /**
     * @return string
     */
    public function getIncomeBenefitsState()
    {
        $stCount = count($this->ndr->getStateBenefitsPresent());
        $statePens = $this->ndr->getReceiveStatePension();
        $otherInc = $this->ndr->getReceiveOtherIncome();
        $compensDamag = $this->ndr->getExpectCompensationDamages();
        $ooCount = count($this->ndr->getOneOffPresent());

        if (
            0 === $stCount
            && null == $statePens && null == $otherInc && null == $compensDamag
            && 0 === $ooCount
        ) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if (null !== $statePens && null !== $otherInc && null !== $compensDamag) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->ndr->getBankAccounts())) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->ndr->getBankAccounts())];
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->ndr->getAssets()) > 0;
        $noAssetsToAdd = $this->ndr->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->ndr->getAssets())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getDebtsState()
    {
        $hasDebts = $this->ndr->getHasDebts();
        if (empty($hasDebts)) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        } elseif (
            'no' == $hasDebts ||
            ('yes' == $hasDebts &&
                count($this->ndr->getDebtsWithValidAmount()) > 0) &&
            !empty($this->ndr->getDebtManagement())
        ) {
            return ['state' => self::STATE_DONE];
        } else {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => count($this->ndr->getDebtsWithValidAmount())];
        }
    }

    public function getActionsState()
    {
        $filled = count(array_filter([
            $this->ndr->getActionGiveGiftsToClient(),
            $this->ndr->getActionPropertyBuy(),
            $this->ndr->getActionPropertyMaintenance(),
            $this->ndr->getActionPropertySellingRent(),
        ]));

        switch ($filled) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case 4:
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }

    /**
     * @return string
     */
    public function getOtherInfoState()
    {
        if (null === $this->ndr->getActionMoreInfo()) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
    }

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        return array_filter($this->getSectionStatus(), function ($e) {
            return self::STATE_DONE != $e;
        });
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return 0 === count($this->getRemainingSections());
    }

    /**
     * @return string $status | null
     */
    public function getSubmitState()
    {
        return [
            'state' => $this->isReadyToSubmit() ? self::STATE_DONE : self::STATE_NOT_STARTED,
            'nOfRecords' => 0,
        ];
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if (
            0 === count(array_filter($this->getSectionStatus(), function ($e) {
                return self::STATE_NOT_STARTED != $e;
            }))
        ) {
            return 'notStarted';
        }

        if ($this->isReadyToSubmit()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }

    public function getClientBenefitsCheckState(): array
    {
        $benefitsCheck = $this->ndr->getClientBenefitsCheck();

        $answers = $benefitsCheck ? [
            'whenChecked' => $benefitsCheck->getWhenLastCheckedEntitlement(),
            'doOthersReceiveIncome' => $benefitsCheck->getDoOthersReceiveMoneyOnClientsBehalf(),
            'incomeTypes' => $benefitsCheck->getTypesOfMoneyReceivedOnClientsBehalf()->count() > 0 ? true : null,
        ] : [];

        switch (count(array_filter($answers))) {
            case 0:
                return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
            case 2:
                if (in_array($answers['doOthersReceiveIncome'], [ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW, ClientBenefitsCheck::OTHER_MONEY_NO])) {
                    return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
                } else {
                    return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
                }
                // no break
            case 3:
                return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
            default:
                return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }
    }
}

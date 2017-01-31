<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\Odr;

class OdrStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /** @var Odr */
    private $odr;

    public function __construct(Odr $odr)
    {
        $this->odr = $odr;
    }

    /** @return string */
    public function getVisitsCareState()
    {
        if (!$this->odr->getVisitsCare()) {
            return self::STATE_NOT_STARTED;
        }
        if ($this->odr->getVisitsCare()->missingInfo()) {
            return self::STATE_INCOMPLETE;
        }
        return self::STATE_DONE;
    }

    /**
     * @return string
     */
    public function getExpensesState()
    {
        if (count($this->odr->getExpenses()) > 0 || $this->odr->getPaidForAnything() === 'no') {
            return self::STATE_DONE;
        }

        return self::STATE_NOT_STARTED;
    }

    /**
     * @return string
     */
    public function getIncomeBenefitsState()
    {
        $stCount = count($this->odr->getStateBenefitsPresent());
        $statePens = $this->odr->getReceiveStatePension();
        $otherInc = $this->odr->getReceiveOtherIncome();
        $compensDamag = $this->odr->getExpectCompensationDamages();
        $ooCount = count($this->odr->getOneOffPresent());

        if ($stCount === 0
            && $statePens == null && $otherInc == null && $compensDamag == null
            && $ooCount === 0
        ) {
            return self::STATE_NOT_STARTED;
        }


        if ($statePens !== null && $otherInc !== null && $compensDamag !== null) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->odr->getBankAccounts())) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->odr->getAssets()) > 0;
        $noAssetsToAdd = $this->odr->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return self::STATE_NOT_STARTED;
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    /** @return string */
    public function getDebtsState()
    {
        $hasDebts = $this->odr->getHasDebts();

        if (empty($hasDebts)) {
            return self::STATE_NOT_STARTED;
        }

        $debtsSectionComplete = in_array($hasDebts, ['yes', 'no']);
        if ($debtsSectionComplete) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    public function getActionsState()
    {
        $filled = count(array_filter([
            $this->odr->getActionGiveGiftsToClient(),
            $this->odr->getActionPropertyBuy(),
            $this->odr->getActionPropertyMaintenance(),
            $this->odr->getActionPropertySellingRent()
        ]));

        switch ($filled) {
            case 0:
                return self::STATE_NOT_STARTED;
            case 4:
                return self::STATE_DONE;
            default:
                return self::STATE_INCOMPLETE;
        }
    }

    /**
     * @return string
     */
    public function getOtherInfoState()
    {
        if ($this->odr->getActionMoreInfo() === null) {
            return self::STATE_NOT_STARTED;
        }

        return self::STATE_DONE;
    }

    /**
     * @return array
     */
    private function getSectionStatus()
    {
        return [
            'visitsCare' => $this->getVisitsCareState(),
            'expenses' => $this->getExpensesState(),
            'incomeBenefits' => $this->getIncomeBenefitsState(),
            'assets' => $this->getAssetsState(),
            'bankAccounts' => $this->getBankAccountsState(),
            'debts' => $this->getDebtsState(),
            'actions' => $this->getActionsState(),
            'otherInfo' => $this->getOtherInfoState(),
        ];
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

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0;
    }

    /**
     * //TODO replace with isReadyToSubmit
     *
     * @return string $status | null
     */
    public function getStatus()
    {
        $startedSections = array_filter($this->getSectionStatus(), function($e) {
            return $e != self::STATE_NOT_STARTED;
        });
        if (count($startedSections) === 0) {
            return 'notStarted';
        }

        if ($this->isReadyToSubmit()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}

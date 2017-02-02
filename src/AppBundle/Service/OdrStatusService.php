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
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }
        if ($this->odr->getVisitsCare()->missingInfo()) {
            return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
    }

    /**
     * @return string
     */
    public function getExpensesState()
    {
        $nOfExpenses = count($this->odr->getExpenses());
        if ($nOfExpenses > 0 || $this->odr->getPaidForAnything() === 'no') {
            return ['state' => self::STATE_DONE, 'nOfRecords' => $nOfExpenses];
        }

        return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
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
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }


        if ($statePens !== null && $otherInc !== null && $compensDamag !== null) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getBankAccountsState()
    {
        if (empty($this->odr->getBankAccounts())) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->odr->getBankAccounts())];
    }

    /** @return string */
    public function getAssetsState()
    {
        $hasAtLeastOneAsset = count($this->odr->getAssets()) > 0;
        $noAssetsToAdd = $this->odr->getNoAssetToAdd();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        if ($hasAtLeastOneAsset || $noAssetsToAdd) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->odr->getAssets())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    /** @return string */
    public function getDebtsState()
    {
        $hasDebts = $this->odr->getHasDebts();

        if (empty($hasDebts)) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        $debtsSectionComplete = in_array($hasDebts, ['yes', 'no']);
        if ($debtsSectionComplete) {
            return ['state' => self::STATE_DONE, 'nOfRecords' => count($this->odr->getDebtsWithValidAmount())];
        }

        return ['state' => self::STATE_INCOMPLETE, 'nOfRecords' => 0];
    }

    public function getActionsState()
    {
        $filled = count(array_filter([
            $this->odr->getActionGiveGiftsToClient(),
            $this->odr->getActionPropertyBuy(),
            $this->odr->getActionPropertyMaintenance(),
            $this->odr->getActionPropertySellingRent(),
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
        if ($this->odr->getActionMoreInfo() === null) {
            return ['state' => self::STATE_NOT_STARTED, 'nOfRecords' => 0];
        }

        return ['state' => self::STATE_DONE, 'nOfRecords' => 0];
    }

    /**
     * @return array
     */
    private function getSectionStatus()
    {
        return [
            'visitsCare'     => $this->getVisitsCareState()['state'],
            'expenses'       => $this->getExpensesState()['state'],
            'incomeBenefits' => $this->getIncomeBenefitsState()['state'],
            'assets'         => $this->getAssetsState()['state'],
            'bankAccounts'   => $this->getBankAccountsState()['state'],
            'debts'          => $this->getDebtsState()['state'],
            'actions'        => $this->getActionsState()['state'],
            'otherInfo'      => $this->getOtherInfoState()['state'],
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
     * @return string $status | null
     */
    public function getSubmitState()
    {
        return [
            'state'      => $this->isReadyToSubmit() ? self::STATE_DONE : self::STATE_NOT_STARTED,
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

        if ($this->isReadyToSubmit()) {
            return 'readyToSubmit';
        } else {
            return 'notFinished';
        }
    }
}

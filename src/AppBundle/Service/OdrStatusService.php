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

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        $states = [
            'visitsCare' => $this->getVisitsCareState(),
            'finance' => $this->getFinanceState(),
            //...
        ];

        return array_filter($states, function ($e) {
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
        if ($this->isReadyToSubmit()) {
            return 'readyToSubmit';
        }

        if (!$this->odr->getVisitsCare()) {
            return 'notStarted';
        }

        return 'notFinished';
    }

    /** @return string */
    public function getVisitsCareState()
    {
        if (!$this->odr->getVisitsCare() || $this->odr->getVisitsCare()->missingInfo()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }

    public function getFinanceState()
    {
        if (empty($this->odr->getBankAccounts())) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }
}

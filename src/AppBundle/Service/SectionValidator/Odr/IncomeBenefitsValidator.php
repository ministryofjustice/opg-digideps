<?php

namespace AppBundle\Service\SectionValidator\Odr;
use AppBundle\Entity\Odr\Odr;

class IncomeBenefitsValidator
{
    /**
     * @var Odr
     */
    private $odr;

    /**
     * @param Odr $odr
     */
    public function __construct(Odr $odr)
    {
        $this->odr = $odr;
    }

    /**
     * @param $question
     * @return bool
     */
    public function missing($question)
    {
        switch ($question) {
            case 'stateBenefits':
                return count($this->odr->recordsPresent($this->odr->getStateBenefits())) === 0;
            case 'receiveStatePension':
                return $this->odr->getReceiveStatePension() === null;
            case 'receiveOtherIncome':
                return $this->odr->getReceiveOtherIncome() === null;
            case 'expectCompensationDamages':
                return $this->odr->getExpectCompensationDamages() === null;
            case 'oneOff':
                return count($this->odr->recordsPresent($this->odr->getOneOff())) === 0;
        }
    }

    /**
     * @return int
     */
    public function countMissing()
    {
        return count(array_filter([
            count($this->odr->recordsPresent($this->odr->getStateBenefits())) === 0,
            $this->odr->getReceiveStatePension() === null,
            $this->odr->getReceiveOtherIncome() === null,
            $this->odr->getExpectCompensationDamages() === null,
            count($this->odr->recordsPresent($this->odr->getOneOff())) === 0,
        ]));
    }

}
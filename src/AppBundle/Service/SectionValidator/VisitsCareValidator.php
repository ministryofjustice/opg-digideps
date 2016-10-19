<?php

namespace AppBundle\Service\SectionValidator;
use AppBundle\Entity\Report\VisitsCare;

/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 19/10/2016
 * Time: 12:23
 */
class VisitsCareValidator
{
    /**
     * @var VisitsCare
     */
    private $visitsCare;

    /**
     * VisitsCareValidator constructor.
     * @param VisitsCare $visitsCare
     */
    public function __construct(VisitsCare $visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }

    public function missing($question)
    {
        switch ($question) {
            case 'doYouLiveWithClient':
                return $this->visitsCare->getDoYouLiveWithClient() === null;
            case 'doesClientReceivePaidCare':
                return $this->visitsCare->getDoesClientReceivePaidCare() === null;
            case 'whoIsDoingTheCaring':
                return $this->visitsCare->getWhoIsDoingTheCaring() === null;
            case 'doesClientHaveACarePlan':
                return $this->visitsCare->getDoesClientHaveACarePlan() === null;
        }
    }

    public function countMissing()
    {
        return count(array_filter([
            $this->visitsCare->getDoYouLiveWithClient() === null,
            $this->visitsCare->getDoesClientReceivePaidCare() === null,
            $this->visitsCare->getWhoIsDoingTheCaring() === null,
            $this->visitsCare->getDoesClientHaveACarePlan() === null,
        ]));
    }

}
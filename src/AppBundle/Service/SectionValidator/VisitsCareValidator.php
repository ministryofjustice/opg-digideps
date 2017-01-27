<?php

namespace AppBundle\Service\SectionValidator;

use AppBundle\Entity\Report\VisitsCare;

class VisitsCareValidator
{
    /**
     * @var VisitsCare
     */
    private $visitsCare;

    /**
     * VisitsCareValidator constructor.
     *
     * @param VisitsCare $visitsCare
     */
    public function __construct(VisitsCare $visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }

    /**
     * @param $question
     *
     * @return bool
     */
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

    /**
     * @return int
     */
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

<?php

namespace AppBundle\Service\SectionValidator;
use AppBundle\Entity\Report\Action;

class ActionsValidator
{
    /**
     * @var Action
     */
    private $action;

    /**
     * VisitsCareValidator constructor.
     * @param VisitsCare $action
     */
    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function missing($question)
    {
        switch ($question) {
            case 'doYouExpectFinancialDecisions':
                return $this->action->getDoYouExpectFinancialDecisions() === null;
            case 'doYouHaveConcerns':
                return $this->action->getDoYouHaveConcerns() === null;
        }
    }

    public function countMissing()
    {
        return count(array_filter([
            $this->action->getDoYouExpectFinancialDecisions() === null,
            $this->action->getDoYouHaveConcerns() === null,
        ]));
    }

}
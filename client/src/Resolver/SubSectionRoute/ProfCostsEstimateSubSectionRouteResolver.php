<?php

namespace App\Resolver\SubSectionRoute;

use App\Entity\Report\Report;
use App\Entity\Report\Status;

class ProfCostsEstimateSubSectionRouteResolver
{
    const SUMMARY_ROUTE = 'prof_deputy_costs_estimate_summary';
    const BREAKDOWN_ROUTE = 'prof_deputy_costs_estimate_breakdown';
    const MORE_INFO_ROUTE = 'prof_deputy_costs_estimate_more_info';

    /**
     * @param $state
     *
     * @return string
     */
    public function resolve(Report $report, $state)
    {
        if ($this->sectionNotStarted($state)) {
            return;
        }

        if ($this->sectionIsComplete($state)) {
            return self::SUMMARY_ROUTE;
        }

        if ($this->breakdownSubsectionIsIncomplete($report, $state)) {
            return self::BREAKDOWN_ROUTE;
        }

        if ($this->moreInfoSubsectionIsIncomplete($report, $state)) {
            return self::MORE_INFO_ROUTE;
        }
    }

    /**
     * @param $state
     *
     * @return bool
     */
    private function sectionNotStarted($state)
    {
        return Status::STATE_NOT_STARTED === $state;
    }

    /**
     * @param $state
     *
     * @return bool
     */
    private function sectionIsComplete($state)
    {
        return Status::STATE_DONE === $state;
    }

    /**
     * @param $state
     *
     * @return bool
     */
    private function breakdownSubsectionIsIncomplete(Report $report, $state)
    {
        return Status::STATE_INCOMPLETE === $state && empty($report->getProfDeputyEstimateCosts());
    }

    /**
     * @param $state
     *
     * @return bool
     */
    private function moreInfoSubsectionIsIncomplete(Report $report, $state)
    {
        return Status::STATE_INCOMPLETE === $state && null === $report->getProfDeputyCostsEstimateHasMoreInfo();
    }
}

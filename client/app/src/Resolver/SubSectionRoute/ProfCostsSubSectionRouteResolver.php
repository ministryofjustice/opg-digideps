<?php

namespace OPG\Digideps\Frontend\Resolver\SubSectionRoute;

use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\Status;

class ProfCostsSubSectionRouteResolver
{
    public const string SUMMARY_ROUTE = 'prof_deputy_costs_summary';
    public const string COSTS_RECEIVED_ROUTE = 'prof_deputy_costs_received';
    public const string SCCO_AMOUNT_ROUTE = 'prof_deputy_costs_amount_scco';
    public const string INTERIM_EXISTS_ROUTE = 'prof_deputy_costs_inline_interim_19b_exists';
    public const string INTERIM_ROUTE = 'prof_deputy_costs_inline_interim_19b';
    public const string BREAKDOWN_ROUTE = 'prof_deputy_costs_breakdown';

    public function resolve(Report $report, $state): ?string
    {
        if ($this->sectionIsComplete($state)) {
            return self::SUMMARY_ROUTE;
        }

        if ($this->sectionNotStarted($state) || $this->previousRecievedExistsSubsectionIsIncomplete($report)) {
            return null;
        }

        if ($this->routeIsFixedCosts($report)) {
            return $this->determineCurrentFixedCostSection($report);
        }

        if (!$this->routeIsFixedCosts($report)) {
            return $this->determineCurrentNonFixedCostSection($report);
        }

        return self::SUMMARY_ROUTE;
    }

    private function sectionNotStarted($state): bool
    {
        return $state === Status::STATE_NOT_STARTED || ($state !== Status::STATE_INCOMPLETE && $state !== Status::STATE_DONE);
    }

    private function sectionIsComplete($state): bool
    {
        return $state === Status::STATE_DONE;
    }

    private function previousRecievedExistsSubsectionIsIncomplete(Report $report): bool
    {
        return !$report->getProfDeputyCostsHasPrevious();
    }

    private function routeIsFixedCosts(Report $report): bool
    {
        return $report->hasProfDeputyCostsHowChargedFixedOnly();
    }

    private function determineCurrentFixedCostSection(Report $report): ?string
    {
        if ($this->fixedCostsSubsectionIsIncomplete($report)) {
            return self::COSTS_RECEIVED_ROUTE;
        }

        if ($this->breakdownCostsIsIncomplete($report)) {
            return self::BREAKDOWN_ROUTE;
        }

        return null;
    }

    private function determineCurrentNonFixedCostSection(Report $report): ?string
    {
        if ($this->interimExistsSubsectionIsIncomplete($report)) {
            return self::INTERIM_EXISTS_ROUTE;
        }

        if ($this->interimExists($report) && $this->interimSubsectionIsIncomplete($report)) {
            return self::INTERIM_ROUTE;
        }

        if (!$this->interimExists($report) && $this->fixedCostsSubsectionIsIncomplete($report)) {
            return self::COSTS_RECEIVED_ROUTE;
        }

        if ($this->amountSccoSubsectionIsIncomplete($report)) {
            return self::SCCO_AMOUNT_ROUTE;
        }

        if ($this->breakdownCostsIsIncomplete($report)) {
            return self::BREAKDOWN_ROUTE;
        }

        return null;
    }

    private function fixedCostsSubsectionIsIncomplete(Report $report): bool
    {
        return !$report->getProfDeputyFixedCost();
    }

    private function amountSccoSubsectionIsIncomplete(Report $report): bool
    {
        return !$report->getProfDeputyCostsAmountToScco();
    }

    private function interimExistsSubsectionIsIncomplete(Report $report): bool
    {
        return !$report->getProfDeputyCostsHasInterim();
    }

    private function interimExists(Report $report): bool
    {
        $getProfDeputyCostsHasInterimLower = is_null($report->getProfDeputyCostsHasInterim()) ? '' : strtolower($report->getProfDeputyCostsHasInterim());

        return $getProfDeputyCostsHasInterimLower == 'yes';
    }

    private function interimSubsectionIsIncomplete(Report $report): bool
    {
        return empty($report->getProfDeputyInterimCosts());
    }

    private function breakdownCostsIsIncomplete(Report $report): bool
    {
        return !$report->hasProfDeputyOtherCosts();
    }
}

<?php

namespace Tests\OPG\Digideps\Frontend\Unit\Resolver\SubSectionRoute;

use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\Status;
use OPG\Digideps\Frontend\Resolver\SubSectionRoute\ProfCostsSubSectionRouteResolver;
use PHPUnit\Framework\TestCase;

class ProfCostsSubSectionRouteResolverTest extends TestCase
{
    private ProfCostsSubSectionRouteResolver $sut;

    public function setUp(): void
    {
        $this->sut = new ProfCostsSubSectionRouteResolver();
    }

    public function testReturnsNullIfSectionIsNotStarted(): void
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_NOT_STARTED);

        $this->assertNull($route);
    }

    public function testReturnsSummaryRouteIfSectionIsComplete(): void
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_DONE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::SUMMARY_ROUTE, $route);
    }


    public function testReturnsPreviousFeesExistRouteSubSectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(null);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertNull($route);
    }

    // Fixed cost route tests

    public function testReturnsCostsReceivedRouteWhenFixedCostsSubsectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');

        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::COSTS_RECEIVED_ROUTE, $route);
    }

    public function testReturnsBreakdownRouteWhenFixedCostsSubsectionIsCompleteAndNoBreakdownCostsEntered(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED)->setProfDeputyFixedCost(999.00);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::BREAKDOWN_ROUTE, $route);
    }

    // Non fixed costs route tests

    public function testReturnsInterimExistsRouteWhenInterimExistsSubsectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHasInterim(null);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::INTERIM_EXISTS_ROUTE, $route);
    }

    public function testReturnsInterimRouteWhenInterimExistsAndSubsectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH);
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHasInterim('yes');
        $report->setProfDeputyInterimCosts([]);
        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::INTERIM_ROUTE, $route);
    }

    public function testReturnsCostsReceivedRouteWhenInterimDoesntExistAndFixedCostSubsectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost(0);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::COSTS_RECEIVED_ROUTE, $route);
    }


    public function testReturnsSccoAmountRouteWhenAmountSccoSubsectionIsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost(123.00)
            ->setProfDeputyCostsAmountToScco(0);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::SCCO_AMOUNT_ROUTE, $route);
    }

    public function testReturnsBreakdownRouteWhenBreakdownCostsIncomplete(): void
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious('true');
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost(123.00)
            ->setProfDeputyCostsAmountToScco(123.45);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::BREAKDOWN_ROUTE, $route);
    }
}

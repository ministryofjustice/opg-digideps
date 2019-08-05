<?php

namespace AppBundle\Resolver\SubSectionRoute;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\Status;
use PHPUnit\Framework\TestCase;

class ProfCostsSubSectionRouteResolverTest extends TestCase
{
    /** @var ProfCostsSubSectionRouteResolver */
    private $sut;

    public function setUp()
    {
        $this->sut = new ProfCostsSubSectionRouteResolver();
    }

    public function testReturnsNullIfSectionIsNotStarted()
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_NOT_STARTED);

        $this->assertNull($route);
    }

    public function testReturnsSummaryRouteIfSectionIsComplete()
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_DONE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::SUMMARY_ROUTE, $route);
    }


    public function testReturnsPreviousFeesExistRouteSubSectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(null);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::PREVIOUS_RECEIVED_EXISTS_ROUTE, $route);
    }

    // Fixed cost route tests

    public function testReturnsCostsReceivedRouteWhenFixedCostsSubsectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);

        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::COSTS_RECEIVED_ROUTE, $route);
    }

    public function testReturnsBreakdownRouteWhenFixedCostsSubsectionIsCompleteAndNoBreakdownCostsEntered()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED)->setProfDeputyFixedCost(999.00);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::BREAKDOWN_ROUTE, $route);
    }

    // Non fixed costs route tests

    public function testReturnsInterimExistsRouteWhenInterimExistsSubsectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::INTERIM_EXISTS_ROUTE, $route);
    }

    public function testReturnsInterimRouteWhenInterimExistsAndSubsectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH);
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHasInterim('yes');
        $report->setProfDeputyInterimCosts([]);
        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::INTERIM_ROUTE, $route);
    }

    public function testReturnsCostsReceivedRouteWhenInterimDoesntExistAndFixedCostSubsectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost([]);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::COSTS_RECEIVED_ROUTE, $route);
    }


    public function testReturnsSccoAmountRouteWhenAmountSccoSubsectionIsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost(123.00)
            ->setProfDeputyCostsAmountToScco(false);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::SCCO_AMOUNT_ROUTE, $route);
    }

    public function testReturnsBreakdownRouteWhenBreakdownCostsIncomplete()
    {
        $report = new Report();
        $report->setProfDeputyCostsHasPrevious(true);
        $report->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_BOTH)
            ->setProfDeputyCostsHasInterim('no')
            ->setProfDeputyFixedCost(123.00)
            ->setProfDeputyCostsAmountToScco('123.45');

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsSubSectionRouteResolver::BREAKDOWN_ROUTE, $route);
    }
}

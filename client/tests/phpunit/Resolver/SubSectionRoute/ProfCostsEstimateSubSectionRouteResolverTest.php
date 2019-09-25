<?php

namespace AppBundle\Resolver\SubSectionRoute;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\Status;
use PHPUnit\Framework\TestCase;

class ProfCostsEstimateSubSectionRouteResolverTest extends TestCase
{
    /** @var ProfCostsEstimateSubSectionRouteResolver */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new ProfCostsEstimateSubSectionRouteResolver();
    }

    public function testReturnsNullIfSectionIsNotStarted()
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_NOT_STARTED);

        $this->assertNull($route);
    }

    public function testReturnsSummaryRouteIfSectionIsComplete()
    {
        $route = $this->sut->resolve(new Report(), Status::STATE_DONE);

        $this->assertEquals(ProfCostsEstimateSubSectionRouteResolver::SUMMARY_ROUTE, $route);
    }

    public function testReturnsBreakdownRouteIfSectionIsIncompleteAndBreakdownCostsNotEntered()
    {
        $report = (new Report())->setProfDeputyEstimateCosts([]);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsEstimateSubSectionRouteResolver::BREAKDOWN_ROUTE, $route);
    }

    public function testReturnsMoreInfoRouteIfSectionIsIncompleteAndBreakdownCostsEnteredAndMoreInfoNotEntered()
    {
        $report = (new Report())->setProfDeputyEstimateCosts(['foo' => 'bar']);

        $route = $this->sut->resolve($report, Status::STATE_INCOMPLETE);

        $this->assertEquals(ProfCostsEstimateSubSectionRouteResolver::MORE_INFO_ROUTE, $route);
    }
}

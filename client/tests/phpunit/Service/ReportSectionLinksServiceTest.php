<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionLinksServiceTest extends TestCase
{
    /**
     * @var ReportSectionsLinkService
     */
    protected $sut;

    /**
     * Set up the mockservies
     */
    public function setUp(): void
    {
        $this->router = m::mock(RouterInterface::class);
        $this->router->shouldReceive('generate')->withAnyArgs()->andReturnUsing(function ($a, $b) {
            return $a . http_build_query($b);
        });
        $this->report = m::mock(ReportInterface::class);

        $this->report
            ->shouldReceive('getId')->andReturn('1')
            ->shouldReceive('hasSection')->with('decisions')->andReturn(true)
            ->shouldReceive('hasSection')->with('contacts')->andReturn(true)
            ->shouldReceive('hasSection')->with('visitsCare')->andReturn(true)
            ->shouldReceive('hasSection')->with('lifestyle')->andReturn(true)
            ->shouldReceive('hasSection')->with('actions')->andReturn(true)
            ->shouldReceive('hasSection')->with('actions')->andReturn(true)
            ->shouldReceive('hasSection')->with('otherInfo')->andReturn(true)
            ->shouldReceive('hasSection')->with('gifts')->andReturn(true)
            ->shouldReceive('hasSection')->with('bankAccounts')->andReturn(true)
            ->shouldReceive('hasSection')->with('moneyTransfers')->andReturn(true)
            ->shouldReceive('hasSection')->with('moneyIn')->andReturn(true)
            ->shouldReceive('hasSection')->with('moneyOut')->andReturn(true)
            ->shouldReceive('hasSection')->with('moneyInShort')->andReturn(true)
            ->shouldReceive('hasSection')->with('moneyOutShort')->andReturn(true)
            ->shouldReceive('hasSection')->with('assets')->andReturn(true)
            ->shouldReceive('hasSection')->with('debts')->andReturn(true)
            ->shouldReceive('hasSection')->with('documents')->andReturn(true);

        //$this->report->shouldReceive('getType')->andReturn('irrelevant');

        $this->sut = new ReportSectionsLinkService($this->router);
    }

    public function testgetSectionParamsLay()
    {
        $this->report
            ->shouldReceive('hasSection')->with('paDeputyExpenses')->andReturn(false)
            ->shouldReceive('hasSection')->with('profCurrentFees')->andReturn(false)
            ->shouldReceive('hasSection')->with('actions')->andReturn(true)
            ->shouldReceive('hasSection')->with('profDeputyCosts')->andReturn(false)
            ->shouldReceive('hasSection')->with('deputyExpenses')->andReturn(true)
        ;

        $actual = $this->sut->getSectionParams($this->report, 'debts', 1);
        $this->assertEquals('actions', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'documents', +1);
        $this->assertEquals([], $actual);
    }

    public function testgetSectionParamsPa()
    {
        $this->report
            ->shouldReceive('hasSection')->with('paDeputyExpenses')->andReturn(true)
            ->shouldReceive('hasSection')->with('profCurrentFees')->andReturn(false)
            ->shouldReceive('hasSection')->with('deputyExpenses')->andReturn(false)
            ->shouldReceive('hasSection')->with('profDeputyCosts')->andReturn(false)
            ->shouldReceive('hasSection')->with('profDeputyCostsEstimate')->andReturn(false)
        ;

        $actual = $this->sut->getSectionParams($this->report, 'paFeeExpense', +1);
        $this->assertEquals('gifts', $actual['section']);
    }

    public function testgetSectionParamsProf()
    {
        $this->report
            ->shouldReceive('hasSection')->with('paDeputyExpenses')->andReturn(false)
            ->shouldReceive('hasSection')->with('profCurrentFees')->andReturn(false)// currently disabled
            ->shouldReceive('hasSection')->with('profDeputyCosts')->andReturn(true)
            ->shouldReceive('hasSection')->with('deputyExpenses')->andReturn(false)
            ->shouldReceive('hasSection')->with('profDeputyCostsEstimate')->andReturn(true)
        ;

        $actual = $this->sut->getSectionParams($this->report, 'profDeputyCosts', +1);
        $this->assertEquals('profDeputyCostsEstimate', $actual['section']);
    }

    public function tearDown(): void
    {
        m::close();
    }
}

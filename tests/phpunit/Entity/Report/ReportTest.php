<?php

namespace AppBundle\Entity\Report;

use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Report $report */
    private $report;

    /** @var  Account $account */
    private $account;

    protected function setUp()
    {
        $this->report = new Report();
        $this->account = m::mock('AppBundle\Entity\Report\Account');
        $this->account->shouldIgnoreMissing();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider isDueProvider
     * @test
     */
    public function isDue($endDateModifier, $expected)
    {
        $endDate = new \DateTime();
        $endDate->modify($endDateModifier);
        $this->report->setEndDate($endDate);

        $lastMidnight = new \DateTime();
        $lastMidnight->setTime(0, 0, 0);

        $actual = $this->report->isDue();

        $this->assertEquals($expected, $actual);
    }



    /** @test */
    public function hasMoneyInWhenThereIsMoneyIn()
    {
        $transaction1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([100])
            ->getMock();

        $transaction2 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $this->report->setTransactionsIn([$transaction1, $transaction2]);

        $this->assertEquals(true, $this->report->hasMoneyIn());
    }

    /** @test */
    public function hasMoneyInWhenThereIsNoMoneyIn()
    {
        $transaction1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $transaction2 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $this->report->setTransactionsIn([$transaction1, $transaction2]);

        $this->assertEquals(false, $this->report->hasMoneyIn());
    }

    /** @test */
    public function hasMoneyOutWhenThereIsMoneyOut()
    {
        $transaction1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([100])
            ->getMock();

        $transaction2 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $this->report->setTransactionsOut([$transaction1, $transaction2]);

        $this->assertEquals(true, $this->report->hasMoneyOut());
    }

    /** @test */
    public function hasMoneyOutWhenThereIsNoMoneyOut()
    {
        $transaction1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $transaction2 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $this->report->setTransactionsOut([$transaction1, $transaction2]);

        $this->assertEquals(false, $this->report->hasMoneyOut());
    }

    public function getCountValidTotalsProvider()
    {
        return [
            [[], [], 0],
            [['in1' => null], ['out1' => null], 0],
            [['in1' => 123], [], 1],
            [['in1' => 0], [], 1],
            [[], ['out1' => 123], 1],
            [[], ['out1' => 0], 1],
            [['in1' => 123], ['out1' => 123], 2],
            [['in1' => 0, 'in2' => null], ['out1' => 123, 'out2' => null], 2],
        ];
    }

    public function isDueProvider()
    {
        return [
            ['-1 year', true],
            ['-1 day', true],
            ['+0 days', true],

            ['+1 day', false],
            ['+7 day', false],
            ['+14 day', false],
            ['+1 month', false],
            ['+1 year', false],
        ];
    }

    public function testGetAssetsTotalValue()
    {
        $this->report->setAssets([
            m::mock(AssetOther::class, ['getValueTotal'=>1]),
            m::mock(AssetProperty::class, ['getValueTotal'=>2]),
        ]);

        $this->assertEquals(3, $this->report->getAssetsTotalValue());
    }
}

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
        $this->account = m::mock('AppBundle\Entity\Report\BankAccount');
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

        $this->report->setMoneyTransactionsIn([$transaction1, $transaction2]);

        $this->assertEquals(true, $this->report->hasMoneyIn());
    }

    /** @test */
    public function hasMoneyInWhenThereIsNoMoneyIn()
    {
        $this->report->setMoneyTransactionsIn([]);
        $this->assertEquals(false, $this->report->hasMoneyIn());

        $transaction1 = m::mock(MoneyTransfer::class)
            ->shouldIgnoreMissing(true)
            ->getMock();

        $this->report->setMoneyTransactionsIn([$transaction1]);

        $this->assertEquals(true, $this->report->hasMoneyIn());
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
}

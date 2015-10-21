<?php
namespace AppBundle\Entity;

use Mockery as m;
use AppBundle\Entity\Report;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Report $report */
    private $report;
    
    private $account;
    
    protected function setUp()
    {
        $this->report = new Report;
        $this->account = m::mock('AppBundle\Entity\Account');
        $this->account->shouldIgnoreMissing();
    }
    
    public function tearDown()
    {
        m::close();
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
    
    /**
     * @dataProvider isDueProvider
     */
    public function testIsDue($endDateModifier, $expected)
    {
        $endDate = new \DateTime();
        $endDate->modify($endDateModifier);
        $this->report->setEndDate($endDate);
        
        $lastMidnight = new \DateTime;
        $lastMidnight->setTime(0, 0, 0);
        
        $actual = $this->report->isDue();
        
        $this->assertEquals($expected, $actual);
    }
   
    
    public function testGetOutstandingAccounts()
    {
        $this->account->shouldReceive('hasClosingBalance')->times(3)->andReturn(false,true,false);
        
        $this->report->setAccounts([ $this->account, $this->account, $this->account ]);
        
        $accounts = $this->report->getOutstandingAccounts();
        
        $this->assertInternalType('array', $accounts);
        $this->assertEquals(count($accounts),2);
        $this->assertInstanceOf('AppBundle\Entity\Account', $accounts[0]);
    }
    
    /** @test */
    public function sectionCountForProperty() {
        $this->report->setCourtOrderType(REPORT::PROPERTY_AND_AFFAIRS);
        $this->AssertEquals(5, $this->report->getSectionCount());
    }

    /** @test */
    public function sectionCountForOther() {
        $this->report->setCourtOrderType(1);
        $this->AssertEquals(3, $this->report->getSectionCount());
    }
    
}

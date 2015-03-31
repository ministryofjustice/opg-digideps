<?php
namespace AppBundle\Entity;

use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    private $report;
    private $account;
    
    protected function setUp()
    {
        $this->report = new Report;
        $this->account = m::mock('AppBundle\Entity\Account');
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
    
    public function testHasOutstandingAccountsIsTrue()
    {
        $this->account->shouldReceive('hasClosingBalance')->times(3)->andReturn(true,true,false);
        
        $this->report->setAccounts([ $this->account, $this->account, $this->account ]);
        
        $this->assertTrue($this->report->hasOutstandingAccounts());
    }
    
    public function testHasOutstandingAccountsIsFalse()
    {
        $this->account->shouldReceive('hasClosingBalance')->times(3)->andReturn(true,true,true);
        
        $this->report->setAccounts([ $this->account, $this->account, $this->account ]);
        
        $this->assertFalse($this->report->hasOutstandingAccounts());
    }
    
}

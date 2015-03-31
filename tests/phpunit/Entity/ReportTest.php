<?php
namespace AppBundle\Entity;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    private $report;
    
    protected function setUp()
    {
        $this->report = new Report;
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
}

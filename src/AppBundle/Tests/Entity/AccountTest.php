<?php
namespace AppBundle\Entity;

use Mockery as m;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Account 
     */
    private $object;
    
    protected function setUp()
    {
        $this->object = new Account; 
    }
    
    public static function provider()
    {
        return [
            // near the limits
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2015-01-01 00:00:00', 0],
            ['2015-01-01 00:00:00', '2016-01-01 10:58:42', '2015-01-01 00:00:00', 0],
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2016-01-01 23:59:59', 0],
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2016-01-01 10:58:58', 0],
            // between dates
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2015-03-01 23:59:59', 0],
            // out the limits for a few seconds
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2014-12-31 23:59:59', 1],
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2016-01-02 00:00:00', 1],
            // out of the limits for more than 1 day
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2014-10-31 23:59:59', 1],
            ['2015-01-01 10:58:42', '2016-01-01 10:58:42', '2016-03-02 00:00:00', 1],
        ];
    }
    
    /**
     * @dataProvider provider
     */
    public function testIsOpeningDateBetweenReportDates($reportStartDate, $reportEndDate, $accountOpeningDate, $numberOfViolations)
    {
        $this->markTestIncomplete("method removed. Use similar logic for other new methods");
        
        // report mock with start/end dates
        $report = m::mock('AppBundle\Entity\Report');
        $report->shouldReceive('getStartDate')->andReturn(new \DateTime($reportStartDate));
        $report->shouldReceive('getEndDate')->andReturn(new \DateTime($reportEndDate));
        $this->object->setReportObject($report);
        
        $this->object->setOpeningDate(new \DateTime($accountOpeningDate));
        
        // set expected violation on validator mock
        $context = m::mock('Symfony\Component\Validator\ExecutionContextInterface');
        $context->shouldReceive('addViolationAt')->with('openingDate',m::any())->times($numberOfViolations);
        
        $this->object->isOpeningDateBetweenReportDates($context);
        
        m::close();
    }
    
}

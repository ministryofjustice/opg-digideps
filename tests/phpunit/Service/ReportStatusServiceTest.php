<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity\Report;
use AppBundle\Service\ReportStatusService as Rss;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->decision = m::mock(\AppBundle\Entity\Decision::class);
        $this->mc = m::mock(\AppBundle\Entity\MentalCapacity::class);
    }
    
    /**
     * @param array $reportMethods
     * 
     * @return ReportStatusService
     */
    private function getObjectWithReportMocks(array $reportMethods)
    {
        $report = m::mock(Report::class, $reportMethods + [
            'getCourtOrderTypeId' => Report::PROPERTY_AND_AFFAIRS,
            'getAccounts' => [],
            'getAssets' => [],
            'getDecisions' => [],
            'getNoAssetToAdd' => null,
            'getContacts' => null,
            'getReasonForNoContacts' => null,
            'getReasonForNoDecisions' => null,
            'getSafeguarding' => null,
            'getAction' => null,
            'getMentalCapacity' => null,
            'hasMoneyIn' => false,
            'hasMoneyOut' => false,
        ]);
        
        return new Rss($report);
    }
    
    public function testNothingFilled()
    {
        $object = $this->getObjectWithReportMocks([]);
        $expected = ['decisions', 'contacts', 'safeguarding', 'account', 'assets', 'actions'];
        $this->assertEquals($expected, $object->getRemainingSections());
        
        $this->assertEquals(Rss::STATE_NOT_STARTED, $object->getSafeguardingState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $object->getAccountsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $object->getAssetsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $object->getActionsState());
    }
   
    public function testDecisions()
    {
        $object = $this->getObjectWithReportMocks([]);
        $this->assertEquals(Rss::STATE_NOT_STARTED, $object->getDecisionsState());
        
        // incomplete 
        $object = $this->getObjectWithReportMocks([
            'getDecisions' => $this->decision
        ]);
        $this->assertEquals(Rss::STATE_INCOMPLETE, $object->getDecisionsState());
        $this->assertContains('decisions', $object->getRemainingSections());
        
        // incomplete
        $object = $this->getObjectWithReportMocks([
            'getMentalCapacity' => $this->mc
        ]);
        $this->assertEquals(Rss::STATE_INCOMPLETE, $object->getDecisionsState());
        $this->assertContains('decisions', $object->getRemainingSections());
        
        // done
        $object = $this->getObjectWithReportMocks([
            'getMentalCapacity' => $this->mc,
            'getDecisions' => $this->decision
        ]);
        $this->assertEquals(Rss::STATE_DONE, $object->getDecisionsState());
        $this->assertNotContains('decisions', $object->getRemainingSections());
    }
    
    //FOLLOW EXAMPLE ...
        
}

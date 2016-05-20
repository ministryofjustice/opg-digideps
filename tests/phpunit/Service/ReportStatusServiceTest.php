<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity\Report;
use AppBundle\Service\ReportStatusService as Rss;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportStatusService 
     */
    private $object;
    
    public function setUp()
    {
        
        $this->report = m::mock(Report::class, [
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
        $this->object = new Rss($this->report);
    }
    
    public function testNothingFilled()
    {
        $expected = ['decisions', 'contacts', 'safeguarding', 'account', 'assets', 'actions'];
        $this->assertEquals($expected, $this->object->getRemainingSections());
        
        
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getSafeguardingState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getAccountsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getAssetsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getActionsState());
    }

    private function assertIncomplete($state, $section)
    {
        $this->assertEquals(Rss::STATE_INCOMPLETE, $state);
        $this->assertContains($section, $this->object->getRemainingSections());
    }
    
    public function testDecisions()
    {
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getDecisionsState());
        
        $decision = m::mock(\AppBundle\Entity\Decision::class);
        $mc = m::mock(\AppBundle\Entity\MentalCapacity::class);
        
        // incomplete 
        $this->report->shouldReceive('getDecisions')->andReturn([$decision]);
        $this->assertIncomplete($this->object->getDecisionsState(), 'decisions');
        
        // incomplete
        $this->setUp();
        $this->report->shouldReceive('getMentalCapacity')->andReturn($mc);
        $this->assertIncomplete($this->object->getDecisionsState(), 'decisions');
        
        // done
        $this->setUp();
        $this->report->shouldReceive('getDecisions')->andReturn([$decision]);
        $this->report->shouldReceive('getMentalCapacity')->andReturn($mc);
        $this->assertEquals(Rss::STATE_DONE, $this->object->getDecisionsState());
        $this->assertNotContains('decisions', $this->object->getRemainingSections());
    }
    
    //FOLLOW EXAMPLE ...
        
}

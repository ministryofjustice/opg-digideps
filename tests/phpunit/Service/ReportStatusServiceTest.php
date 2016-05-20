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
        
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getContactsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getSafeguardingState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getAccountsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getAssetsState());
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getActionsState());
    }

    public function testDecisions()
    {
        $this->assertEquals(Rss::STATE_NOT_STARTED, $this->object->getDecisionsState());
        
        $d = m::mock(\AppBundle\Entity\Decision::class);
        $mc = m::mock(\AppBundle\Entity\MentalCapacity::class);
        
        $this->report->shouldReceive('getDecisions')->andReturn([$d]);
        $this->assertEquals(Rss::STATE_INCOMPLETE, $this->object->getDecisionsState());
        $this->report->shouldReceive('getMentalCapacity')->andReturn($mc);
        $this->assertEquals(Rss::STATE_DONE, $this->object->getDecisionsState());
        
        $this->assertNotContains('decisions', $this->object->getRemainingSections());
    }
    
    public function testContacts()
    {
        $c = m::mock(\AppBundle\Entity\Contact::class);
        
        $this->report->shouldReceive('getContacts')->andReturn([$c]);
        $this->assertNotContains('contacts', $this->object->getRemainingSections());
    }
    
    public function testContactsReason()
    {
        $this->report->shouldReceive('getReasonForNoContacts')->andReturn('r');
        $this->assertNotContains('contacts', $this->object->getRemainingSections());
    }
       
        
}

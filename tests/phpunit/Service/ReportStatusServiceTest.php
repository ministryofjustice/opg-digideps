<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity\Report;

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
            
        ]);
        $this->object = new ReportStatusService($this->report);
    }
    
    public function testAllMissing()
    {
        $expected = ['decisions', 'contacts', 'safeguarding', 'account', 'assets', 'actions'];
        $this->assertEquals($expected, $this->object->getRemainingSections());
        
        $this->assertStatus('not-started', $this->object->getContactsStatus('contacts'));
    }

//    public function testDecisions()
//    {
//        $d = m::mock(\AppBundle\Entity\Decision::class);
//        
//        $this->report->shouldReceive('getDecisions')->andReturn([$d]);
//        $this->assertNotContains('decisions', $this->object->getRemainingSections());
//    }
    
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

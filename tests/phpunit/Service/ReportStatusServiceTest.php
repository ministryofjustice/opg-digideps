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
   
    public function decisionsProvider()
    {
        $decision = m::mock(\AppBundle\Entity\Decision::class);
        $mc = m::mock(\AppBundle\Entity\MentalCapacity::class);
        
        return [
            [[], Rss::STATE_NOT_STARTED],
            // incomplete
            [['getDecisions' => [$decision]], Rss::STATE_INCOMPLETE],
            [['getReasonForNoDecisions' => 'x'], Rss::STATE_INCOMPLETE],
            [['getMentalCapacity' => $mc], Rss::STATE_INCOMPLETE],
            // done
            [['getMentalCapacity' => $mc, 'getDecisions' => [$decision]], Rss::STATE_DONE],
            [['getMentalCapacity' => $mc, 'getReasonForNoDecisions' => 'x'], Rss::STATE_DONE],
        ];
    }
    
    /**
     * @dataProvider decisionsProvider
     */
    public function testDecisions($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getDecisionsState());
    }
    
        
    public function contactsProvider()
    {
        $contact = m::mock(\AppBundle\Entity\Contact::class);
        
        return [
            [[], Rss::STATE_NOT_STARTED],
            // incomplete
            [['getContacts' => [$contact]], Rss::STATE_DONE],
            [['getReasonForNoContacts' => 'x'], Rss::STATE_DONE],
        ];
    }
    
    /**
     * @dataProvider contactsProvider
     */
    public function testContacts($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getContactsState());
    }
}

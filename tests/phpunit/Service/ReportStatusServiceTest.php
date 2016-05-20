<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity\Report;
use AppBundle\Service\ReportStatusService as Rss;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase
{
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
    
    /**
     * @test
     */
    public function nothingFilled()
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
     * @test
     * @dataProvider decisionsProvider
     */
    public function cecisions($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getDecisionsState());
    }
    
        
    public function contactsProvider()
    {
        $contact = m::mock(\AppBundle\Entity\Contact::class);
        
        return [
            [[], Rss::STATE_NOT_STARTED],
            // done
            [['getContacts' => [$contact]], Rss::STATE_DONE],
            [['getReasonForNoContacts' => 'x'], Rss::STATE_DONE],
        ];
    }
    
    /**
     * @test
     * @dataProvider contactsProvider
     */
    public function contacts($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getContactsState());
    }
    
    public function safegProvider()
    {
        $safegOk = m::mock(\AppBundle\Entity\Safeguarding::class, [
            'missingSafeguardingInfo' => false
        ]);
        
        $safegErr = m::mock(\AppBundle\Entity\Safeguarding::class, [
            'missingSafeguardingInfo' => true
        ]);
        
        return [
            // not started
            [[], Rss::STATE_NOT_STARTED],
            [['getSafeguarding' => $safegErr], Rss::STATE_NOT_STARTED],
            // done
            [['getSafeguarding' => $safegOk], Rss::STATE_DONE],
        ];
    }
    
    /**
     * @test
     * @dataProvider safegProvider
     */
    public function safeg($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getSafeguardingState());
    }
    
    public function accountProvider()
    {
        $accountOk = m::mock(\AppBundle\Entity\Account::class, [
            'hasClosingBalance' => true,
            'hasMissingInformation' => false
        ]);
        
        $accountClosingMissing = m::mock(\AppBundle\Entity\Account::class, [
            'hasClosingBalance' => false,
            'hasMissingInformation' => false
        ]);
        
        $accountMissingInfo = m::mock(\AppBundle\Entity\Account::class, [
            'hasClosingBalance' => true,
            'hasMissingInformation' => true
        ]);
        
        $transfer = m::mock(\AppBundle\Entity\MoneyTransfer::class);
        
        $partial1 = [
                'getAccounts'=>[$accountOk, $accountOk], 
                'hasMoneyIn'=>true, 
                'hasMoneyOut'=>true,
                'getBalanceMismatchExplanation' => null,
                'isTotalsMatch' => false,
                'getNoTransfersToAdd' => null,
                'getMoneyTransfers' => [],
        ];
        
        return [
            // not started
            [[], Rss::STATE_NOT_STARTED],
            [['getAccounts'=>[$accountOk]], Rss::STATE_INCOMPLETE],
            [['getAccounts'=>[$accountClosingMissing]], Rss::STATE_INCOMPLETE],
            [['getAccounts'=>[$accountMissingInfo]], Rss::STATE_INCOMPLETE],
            [['getAccounts'=>[$accountOk]], Rss::STATE_INCOMPLETE],
            [['getAccounts'=>[$accountOk], 'hasMoneyIn'=>true], Rss::STATE_INCOMPLETE],
            [['getAccounts'=>[$accountOk], 'hasMoneyOut'=>true], Rss::STATE_INCOMPLETE],
            [['getMoneyTransfers'=>[$transfer]] + $partial1, Rss::STATE_INCOMPLETE],
            [['getNoTransfersToAdd'=>'x'] + $partial1, Rss::STATE_INCOMPLETE],
            [['isTotalsMatch'=>true] + $partial1, Rss::STATE_INCOMPLETE],
            [['getBalanceMismatchExplanation'=>'x'] + $partial1, Rss::STATE_INCOMPLETE],
            //done
            [['getNoTransfersToAdd'=>'x', 'isTotalsMatch'=>true] + $partial1, Rss::STATE_DONE],
            [['getMoneyTransfers'=>[$transfer], 'isTotalsMatch'=>true] + $partial1, Rss::STATE_DONE],
            [['getMoneyTransfers'=>[$transfer], 'getBalanceMismatchExplanation'=>'x'] + $partial1, Rss::STATE_DONE],
        ];
    }
    
    /**
     * @test
     * @dataProvider accountProvider
     */
    public function account($mocks, $state)
    {
        $object = $this->getObjectWithReportMocks($mocks);
        $this->assertEquals($state, $object->getAccountsState());
    }
}

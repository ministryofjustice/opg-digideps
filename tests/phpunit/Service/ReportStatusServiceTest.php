<?php

namespace AppBundle\Service;

use Mockery as m;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportStatusService as StatusService;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $reportMethods
     *
     * @return ReportStatusService
     */
    private function getReportMocked(array $reportMethods)
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
                'getHasDebts' => null,
                'getDebts' => [],
            ]);

        return new StatusService($report);
    }

    public function decisionsProvider()
    {
        $decision = m::mock(\AppBundle\Entity\Decision::class);
        $mc = m::mock(\AppBundle\Entity\MentalCapacity::class);

        return [
            [[], StatusService::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => [$decision]], StatusService::STATE_INCOMPLETE, false],
            [['getReasonForNoDecisions' => 'x'], StatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mc], StatusService::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mc, 'getDecisions' => [$decision]], StatusService::STATE_DONE, true],
            [['getMentalCapacity' => $mc, 'getReasonForNoDecisions' => 'x'], StatusService::STATE_DONE, true],
        ];
    }

    /**
     * @test
     * @dataProvider decisionsProvider
     */
    public function decisions($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getDecisionsState());
    }

    public function contactsProvider()
    {
        $contact = m::mock(\AppBundle\Entity\Contact::class);

        return [
            [[], StatusService::STATE_NOT_STARTED, false],
            // done
            [['getContacts' => [$contact]], StatusService::STATE_DONE, true],
            [['getReasonForNoContacts' => 'x'], StatusService::STATE_DONE, true],
        ];
    }

    /**
     * @test
     * @dataProvider contactsProvider
     */
    public function contacts($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getContactsState());
    }

    public function safeguardingProvider()
    {
        $safegOk = m::mock(\AppBundle\Entity\Report\Safeguarding::class, [
            'missingSafeguardingInfo' => false,
        ]);

        $safegErr = m::mock(\AppBundle\Entity\Report\Safeguarding::class, [
            'missingSafeguardingInfo' => true,
        ]);

        return [
            // not started
            [[], StatusService::STATE_NOT_STARTED],
            [['getSafeguarding' => $safegErr], StatusService::STATE_NOT_STARTED],
            // done
            [['getSafeguarding' => $safegOk], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider safeguardingProvider
     */
    public function safeguarding($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getSafeguardingState());
    }

    public function accountProvider()
    {
        $accountOk = m::mock(\AppBundle\Entity\Report\Account::class, [
            'hasClosingBalance' => true,
            'hasMissingInformation' => false,
        ]);

        $accountClosingMissing = m::mock(\AppBundle\Entity\Report\Account::class, [
            'hasClosingBalance' => false,
            'hasMissingInformation' => false,
        ]);

        $accountMissingInfo = m::mock(\AppBundle\Entity\Report\Account::class, [
            'hasClosingBalance' => true,
            'hasMissingInformation' => true,
        ]);

        $transfer = m::mock(\AppBundle\Entity\MoneyTransfer::class);

        $partial1 = [
            'getAccounts' => [$accountOk, $accountOk],
            'hasMoneyIn' => true,
            'hasMoneyOut' => true,
            'getBalanceMismatchExplanation' => null,
            'isTotalsMatch' => false,
            'getNoTransfersToAdd' => null,
            'getMoneyTransfers' => [],
        ];

        return [
            // not started
            [[], StatusService::STATE_NOT_STARTED],
            [['getAccounts' => [$accountOk]], StatusService::STATE_INCOMPLETE],
            [['getAccounts' => [$accountClosingMissing]], StatusService::STATE_INCOMPLETE],
            [['getAccounts' => [$accountMissingInfo]], StatusService::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk]], StatusService::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk], 'hasMoneyIn' => true], StatusService::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk], 'hasMoneyOut' => true], StatusService::STATE_INCOMPLETE],
            [['getMoneyTransfers' => [$transfer]] + $partial1, StatusService::STATE_INCOMPLETE],
            [['getNoTransfersToAdd' => 'x'] + $partial1, StatusService::STATE_INCOMPLETE],
            [['isTotalsMatch' => true] + $partial1, StatusService::STATE_INCOMPLETE],
            [['getBalanceMismatchExplanation' => 'x'] + $partial1, StatusService::STATE_INCOMPLETE],
            //done
            [['getNoTransfersToAdd' => 'x', 'isTotalsMatch' => true] + $partial1, StatusService::STATE_DONE],
            [['getMoneyTransfers' => [$transfer], 'isTotalsMatch' => true] + $partial1, StatusService::STATE_DONE],
            // one account does not require trnasfers or transfer explanation
            [['getAccounts' => [$accountOk], 'getBalanceMismatchExplanation' => 'x'] + $partial1, StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider accountProvider
     */
    public function account($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getAccountsState());
    }

    public function assetsDebtsProvider()
    {
        $asset = m::mock(\AppBundle\Entity\Asset::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            // missing sth
            [['getAssets' => [$asset], 'getHasDebts' => null], StatusService::STATE_INCOMPLETE],
            [['getAssets' => [], 'getHasDebts' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getAssets' => [], 'getHasDebts' => 'no'], StatusService::STATE_INCOMPLETE],
            // done
            [['getAssets' => [$asset], 'getHasDebts' => 'yes'], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getHasDebts' => 'no'], StatusService::STATE_DONE],
            [['getNoAssetToAdd' => true, 'getHasDebts' => 'yes'], StatusService::STATE_DONE],
            [['getNoAssetToAdd' => true, 'getHasDebts' => 'no'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider assetsDebtsProvider
     */
    public function assets($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getAssetsState());
    }

    public function actionsProvider()
    {
        $action = m::mock(\AppBundle\Entity\Action::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            // done
            [['getAction' => $action], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider actionsProvider
     */
    public function actions($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getActionsState());
    }

    /**
     * @test
     */
    public function getRemainingSectionsEmpty()
    {
        $object = $this->getReportMocked([]);
        $expected = [
            'decisions' => 'not-started',
            'contacts' => 'not-started',
            'safeguarding' => 'not-started',
            'actions' => 'not-started',
            'accounts' => 'not-started',
            'assets' => 'not-started',
        ];
        $this->assertEquals($expected, $object->getRemainingSections());

        $this->assertFalse($object->isReadyToSubmit());
    }

    public function getRemainingSectionsPartialProvider()
    {
        return [
            // create using last DONE section of each provider
            [array_pop($this->decisionsProvider())[0], 'decisions'],
            [array_pop($this->contactsProvider())[0], 'contacts'],
            [array_pop($this->safeguardingProvider())[0], 'safeguarding'],
            [array_pop($this->accountProvider())[0], 'accounts'],
            [array_pop($this->assetsDebtsProvider())[0], 'assets'],
            [array_pop($this->actionsProvider())[0], 'actions'],
        ];
    }

    /**
     * @test
     * @dataProvider getRemainingSectionsPartialProvider
     */
    public function getRemainingSectionsPartial($provider, $keyRemoved)
    {
        $object = $this->getReportMocked($provider);
        $this->assertArrayNotHasKey($keyRemoved, $object->getRemainingSections());
        $this->assertFalse($object->isReadyToSubmit());
    }

    /**
     * @test
     */
    public function getRemainingSectionsNone()
    {
        $object = $this->getReportMocked(
            array_pop($this->decisionsProvider())[0]
            + array_pop($this->contactsProvider())[0]
            + array_pop($this->safeguardingProvider())[0]
            + array_pop($this->accountProvider())[0]
            + array_pop($this->assetsDebtsProvider())[0]
            + array_pop($this->actionsProvider())[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
    }
}

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
                'getDebts' => []
            ]);

        return new Rss($report);
    }

    public function decisionsProvider()
    {
        $decision = m::mock(\AppBundle\Entity\Decision::class);
        $mc = m::mock(\AppBundle\Entity\MentalCapacity::class);

        return [
            [[], Rss::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => [$decision]], Rss::STATE_INCOMPLETE, false],
            [['getReasonForNoDecisions' => 'x'], Rss::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mc], Rss::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mc, 'getDecisions' => [$decision]], Rss::STATE_DONE, true],
            [['getMentalCapacity' => $mc, 'getReasonForNoDecisions' => 'x'], Rss::STATE_DONE, true],
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
            [[], Rss::STATE_NOT_STARTED, false],
            // done
            [['getContacts' => [$contact]], Rss::STATE_DONE, true],
            [['getReasonForNoContacts' => 'x'], Rss::STATE_DONE, true],
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
        $safegOk = m::mock(\AppBundle\Entity\Safeguarding::class, [
            'missingSafeguardingInfo' => false,
        ]);

        $safegErr = m::mock(\AppBundle\Entity\Safeguarding::class, [
            'missingSafeguardingInfo' => true,
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
     * @dataProvider safeguardingProvider
     */
    public function safeguarding($mocks, $state)
    {
        $object = $this->getReportMocked($mocks);
        $this->assertEquals($state, $object->getSafeguardingState());
    }

    public function accountProvider()
    {
        $accountOk = m::mock(\AppBundle\Entity\Account::class, [
            'hasClosingBalance' => true,
            'hasMissingInformation' => false,
        ]);

        $accountClosingMissing = m::mock(\AppBundle\Entity\Account::class, [
            'hasClosingBalance' => false,
            'hasMissingInformation' => false,
        ]);

        $accountMissingInfo = m::mock(\AppBundle\Entity\Account::class, [
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
            [[], Rss::STATE_NOT_STARTED],
            [['getAccounts' => [$accountOk]], Rss::STATE_INCOMPLETE],
            [['getAccounts' => [$accountClosingMissing]], Rss::STATE_INCOMPLETE],
            [['getAccounts' => [$accountMissingInfo]], Rss::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk]], Rss::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk], 'hasMoneyIn' => true], Rss::STATE_INCOMPLETE],
            [['getAccounts' => [$accountOk], 'hasMoneyOut' => true], Rss::STATE_INCOMPLETE],
            [['getMoneyTransfers' => [$transfer]] + $partial1, Rss::STATE_INCOMPLETE],
            [['getNoTransfersToAdd' => 'x'] + $partial1, Rss::STATE_INCOMPLETE],
            [['isTotalsMatch' => true] + $partial1, Rss::STATE_INCOMPLETE],
            [['getBalanceMismatchExplanation' => 'x'] + $partial1, Rss::STATE_INCOMPLETE],
            //done
            [['getNoTransfersToAdd' => 'x', 'isTotalsMatch' => true] + $partial1, Rss::STATE_DONE],
            [['getMoneyTransfers' => [$transfer], 'isTotalsMatch' => true] + $partial1, Rss::STATE_DONE],
            // one account does not require trnasfers or transfer explanation
            [['getAccounts' => [$accountOk], 'getBalanceMismatchExplanation' => 'x'] + $partial1, Rss::STATE_DONE],
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

    public function assetsProvider()
    {
        $asset = m::mock(\AppBundle\Entity\Asset::class);

        return [
            [[], Rss::STATE_NOT_STARTED],
            // missing sth
            [['getAssets' => [$asset], 'getHasDebts' => null], Rss::STATE_INCOMPLETE],
            [['getAssets' => [], 'getHasDebts' => 'yes'], Rss::STATE_INCOMPLETE],
            [['getAssets' => [], 'getHasDebts' => 'no'], Rss::STATE_INCOMPLETE],
            // done
            [['getAssets' => [$asset], 'getHasDebts' => 'yes'], Rss::STATE_DONE],
            [['getAssets' => [$asset], 'getHasDebts' => 'no'], Rss::STATE_DONE],
            [['getNoAssetToAdd' => true, 'getHasDebts' => 'yes'], Rss::STATE_DONE],
            [['getNoAssetToAdd' => true, 'getHasDebts' => 'no'], Rss::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider assetsProvider
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
            [[], Rss::STATE_NOT_STARTED],
            // done
            [['getAction' => $action], Rss::STATE_DONE],
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
            [array_pop($this->assetsProvider())[0], 'assets'],
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
            + array_pop($this->assetsProvider())[0]
            + array_pop($this->actionsProvider())[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
    }
}

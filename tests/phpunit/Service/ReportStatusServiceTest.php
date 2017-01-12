<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\ReportStatusService as StatusService;
use Mockery as m;

class ReportStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $reportMethods
     *
     * @return ReportStatusService
     */
    private function getStatusServiceWithReportMocked(array $reportMethods)
    {
        $report = m::mock(Report::class, $reportMethods + [
                'getCourtOrderTypeId' => Report::PROPERTY_AND_AFFAIRS,
                'getBankAccounts' => [],
                'getMoneyTransfers' => [],
                'getNoTransfersToAdd' => null,
                'getAssets' => [],
                'getDecisions' => [],
                'getNoAssetToAdd' => null,
                'getContacts' => null,
                'getReasonForNoContacts' => null,
                'getReasonForNoDecisions' => null,
                'getVisitsCare' => null,
                'getAction' => null,
                'getMentalCapacity' => null,
                'hasMoneyIn' => false,
                'hasMoneyOut' => false,
                'getHasDebts' => null,
                'getDebts' => [],
                'isTotalsMatch' => null,
                'getBalanceMismatchExplanation' => null,
                'getType' => Report::TYPE_102,
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
        $object = $this->getStatusServiceWithReportMocked($mocks);
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
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getContactsState());
    }

    public function visitsCareProvider()
    {
        $visitsCareNotMissingInfo = m::mock(\AppBundle\Entity\Report\VisitsCare::class, [
            'missingInfo' => false,
        ]);

        $visitsCareMissingInfo = m::mock(\AppBundle\Entity\Report\VisitsCare::class, [
            'missingInfo' => true,
        ]);

        return [
            // not started
            [[], StatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $visitsCareMissingInfo], StatusService::STATE_INCOMPLETE],
            // done
            [['getVisitsCare' => $visitsCareNotMissingInfo], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider visitsCareProvider
     */
    public function visitsCare($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getVisitsCareState());
    }

    public function bankAccountProvider()
    {
        $account = m::mock(\AppBundle\Entity\Report\Account::class, [
        ]);

        return [
            [['getBankAccounts' => []], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$account]], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider bankAccountProvider
     */
    public function bankAccount($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getBankAccountsState());
    }

    public function moneyTransferProvider()
    {
        $account1 = m::mock(\AppBundle\Entity\Report\Account::class);
        $account2 = m::mock(\AppBundle\Entity\Report\Account::class);
        $mt1 = m::mock(\AppBundle\Entity\Report\MoneyTransfer::class);

        return [
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [$mt1], 'getNoTransfersToAdd' => null], StatusService::STATE_DONE],
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => true], StatusService::STATE_DONE],
            // less than 2 accounts => done
            [['getBankAccounts' => [$account1]], StatusService::STATE_DONE],
            [['getBankAccounts' => []], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyTransferProvider
     */
    public function moneyTransfer($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyTransferState());
    }

    public function moneyInProvider()
    {
        return [
            [['hasMoneyIn' => false], StatusService::STATE_NOT_STARTED],
            [['hasMoneyIn' => true], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyInProvider
     */
    public function moneyIn($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyInState());
    }

    public function moneyOutProvider()
    {
        return [
            [['hasMoneyOut' => false], StatusService::STATE_NOT_STARTED],
            [['hasMoneyOut' => true], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyOutProvider
     */
    public function moneyOut($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyOutState());
    }

    public function assetsProvider()
    {
        $asset = m::mock(\AppBundle\Entity\Asset::class);

        return [
            [['getAssets' => [], 'getNoAssetToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => null], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
            [['getAssets' => [], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider assetsProvider
     */
    public function assets($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getAssetsState());
    }

    public function debtsProvider()
    {
        return [
            [['getHasDebts' => false], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], StatusService::STATE_DONE],
            [['getHasDebts' => 'no'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider debtsProvider
     */
    public function debts($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getDebtsState());
    }


    public function actionsProvider()
    {
        $actionIncomplete = m::mock(\AppBundle\Entity\Action::class, [
            'getDoYouHaveConcerns' => true,
            'getDoYouExpectFinancialDecisions' => false
        ]);

        $actionComplete = m::mock(\AppBundle\Entity\Action::class, [
            'getDoYouHaveConcerns' => true,
            'getDoYouExpectFinancialDecisions' => true
        ]);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getAction' => $actionIncomplete], StatusService::STATE_INCOMPLETE],
            // done
            [['getAction' => $actionComplete], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider actionsProvider
     */
    public function actions($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getActionsState());
    }


    public function balanceMatchesProvider()
    {
        return [
            [['isTotalsMatch' => false, 'getBalanceMismatchExplanation' => null], false],
            [['isTotalsMatch' => true, 'getBalanceMismatchExplanation' => 'something'], true],
            [['isTotalsMatch' => false, 'getBalanceMismatchExplanation' => 'something'], true],
        ];
    }

    /**
     * @test
     * @dataProvider balanceMatchesProvider
     */
    public function balanceMatches($mocks, $expected)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($expected, $object->balanceMatches());
    }


    /**
     * @test
     */
    public function getRemainingSectionsEmpty()
    {
        $object = $this->getStatusServiceWithReportMocked([]);
        $expected = [
            'decisions' => 'not-started',
            'contacts' => 'not-started',
            'visitsCare' => 'not-started',
            //
            'bankAccounts' => 'not-started',
            'moneyIn' => 'not-started',
            'moneyOut' => 'not-started',
            'assets' => 'not-started',
            'debts' => 'not-started',
            //
            'actions' => 'not-started',
        ];
        $this->assertEquals($expected, $object->getRemainingSections());
    }

    public function getRemainingSectionsPartialProvider()
    {
        return [
            // create using last DONE section of each provider
            [array_pop($this->decisionsProvider())[0], 'decisions'],
            [array_pop($this->contactsProvider())[0], 'contacts'],
            [array_pop($this->visitsCareProvider())[0], 'visitsCare'],
            //
            [array_pop($this->bankAccountProvider())[0], 'accounts'],
            [array_pop($this->moneyTransferProvider())[0], 'moneyTransfers'],
            [array_pop($this->MoneyInProvider())[0], 'accounts'],
            [array_pop($this->MoneyOutProvider())[0], 'accounts'],
            [array_pop($this->assetsProvider())[0], 'assets'],
            [array_pop($this->debtsProvider())[0], 'debts'],
            //
            [array_pop($this->actionsProvider())[0], 'actions'],
        ];
    }

    /**
     * @test
     * @dataProvider getRemainingSectionsPartialProvider
     */
    public function getRemainingSectionsPartial($provider, $keyRemoved)
    {
        $object = $this->getStatusServiceWithReportMocked($provider);
        $this->assertArrayNotHasKey($keyRemoved, $object->getRemainingSections());
    }

    public function getRemainingSectionsProvider()
    {
        return [
            // 102 all missing
            [Report::TYPE_102, [
                'getDecisionsState' => StatusService::STATE_INCOMPLETE,
                'getContactsState' => StatusService::STATE_INCOMPLETE,
                'getVisitsCareState' => StatusService::STATE_INCOMPLETE,
                'getActionsState' => StatusService::STATE_INCOMPLETE,
                'getActionsState' => StatusService::STATE_INCOMPLETE,
                'getBankAccountsState' => StatusService::STATE_INCOMPLETE,
                'getMoneyTransferState' => StatusService::STATE_INCOMPLETE,
                'getMoneyInState' => StatusService::STATE_INCOMPLETE,
                'getMoneyOutState' => StatusService::STATE_INCOMPLETE,
                'getAssetsState' => StatusService::STATE_INCOMPLETE,
                'getDebtsState' => StatusService::STATE_INCOMPLETE,
            ], [
                'decisions' => 'incomplete',
                'contacts' => 'incomplete',
                'visitsCare' => 'incomplete',
                'actions' => 'incomplete',
                'bankAccounts' => 'incomplete',
                'moneyTransfers' => 'incomplete',
                'moneyIn' => 'incomplete',
                'moneyOut' => 'incomplete',
                'assets' => 'incomplete',
                'debts' => 'incomplete',
            ]],
            // 102: all complete
            [Report::TYPE_102, [
                'getDecisionsState' => StatusService::STATE_DONE,
                'getContactsState' => StatusService::STATE_DONE,
                'getVisitsCareState' => StatusService::STATE_DONE,
                'getActionsState' => StatusService::STATE_DONE,
                'getActionsState' => StatusService::STATE_DONE,
                'getBankAccountsState' => StatusService::STATE_DONE,
                'getMoneyTransferState' => StatusService::STATE_DONE,
                'getMoneyInState' => StatusService::STATE_DONE,
                'getMoneyOutState' => StatusService::STATE_DONE,
                'getAssetsState' => StatusService::STATE_DONE,
                'getDebtsState' => StatusService::STATE_DONE,
            ], []],
            // 103 all missing
            [Report::TYPE_103, [
                'getDecisionsState' => StatusService::STATE_INCOMPLETE,
                'getContactsState' => StatusService::STATE_INCOMPLETE,
                'getVisitsCareState' => StatusService::STATE_INCOMPLETE,
                'getActionsState' => StatusService::STATE_INCOMPLETE,
                'getActionsState' => StatusService::STATE_INCOMPLETE,
                'getBankAccountsState' => StatusService::STATE_INCOMPLETE,
                //note: getMoneyTransferState not there
                'getMoneyInState' => StatusService::STATE_INCOMPLETE,
                'getMoneyOutState' => StatusService::STATE_INCOMPLETE,
                'getAssetsState' => StatusService::STATE_INCOMPLETE,
                'getDebtsState' => StatusService::STATE_INCOMPLETE,
            ], [
                'decisions' => 'incomplete',
                'contacts' => 'incomplete',
                'visitsCare' => 'incomplete',
                'actions' => 'incomplete',
                'bankAccounts' => 'incomplete',
                // note: moneyTransfers not in 103
                'moneyIn' => 'incomplete',
                'moneyOut' => 'incomplete',
                'assets' => 'incomplete',
                'debts' => 'incomplete',
            ]],
            // 103: all complete
            [Report::TYPE_103, [
                'getDecisionsState' => StatusService::STATE_DONE,
                'getContactsState' => StatusService::STATE_DONE,
                'getVisitsCareState' => StatusService::STATE_DONE,
                'getActionsState' => StatusService::STATE_DONE,
                'getActionsState' => StatusService::STATE_DONE,
                'getBankAccountsState' => StatusService::STATE_DONE,
                'getMoneyInState' => StatusService::STATE_DONE,
                'getMoneyOutState' => StatusService::STATE_DONE,
                'getAssetsState' => StatusService::STATE_DONE,
                'getDebtsState' => StatusService::STATE_DONE,
            ], []],
        ];
    }

    /**
     * @test
     * @dataProvider getRemainingSectionsProvider
     */
    public function getRemainingSections($reportType, $mocks, $expected)
    {
        $report = m::mock(Report::class, [
            'getCourtOrderTypeId' => Report::PROPERTY_AND_AFFAIRS,
            'getType' => $reportType
        ]);
        $object = m::mock(ReportStatusService::class . '[' . implode(',', array_keys($mocks)) . ']', [$report]);

        foreach ($mocks as $method => $return) {
            $object->shouldReceive($method)->times(1)->andReturn($return);
        }

        $actual = $object->getRemainingSections();
        ksort($actual);
        ksort($expected);
        $this->assertEquals($expected, $actual);
    }

    public function isReadyToSubmitProvider()
    {
        return [
            [['getRemainingSections' => ['s1'], 'balanceMatches' => false], false],
            [['getRemainingSections' => [], 'balanceMatches' => false], false],
            [['getRemainingSections' => [], 'balanceMatches' => true], true],
        ];
    }

    /**
     * @test
     * @dataProvider isReadyToSubmitProvider
     */
    public function isReadyToSubmit($data, $expected)
    {
        $report = m::mock(Report::class);
        $object = m::mock(ReportStatusService::class . '[getRemainingSections,balanceMatches]', [$report]);

        foreach ($data as $method => $return) {
            $object->shouldReceive($method)->andReturn($return);
        }

        $this->assertEquals($expected, $object->isReadyToSubmit());
    }

}

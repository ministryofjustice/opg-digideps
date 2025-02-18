<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\Client;
use App\Entity\Contact;
use App\Entity\Decision;
use App\Entity\MentalCapacity;
use App\Entity\Report\Account;
use App\Entity\Report\Action;
use App\Entity\Report\Debt;
use App\Entity\Report\Document;
use App\Entity\Report\MoneyShortCategory;
use App\Entity\Report\MoneyTransactionShort;
use App\Entity\Report\MoneyTransfer;
use App\Entity\Report\Report;
use App\Entity\Report\VisitsCare;
use App\Service\ReportStatusService as StatusService;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ReportStatusServiceTest extends TestCase
{
    use ProphecyTrait;

    /** @var Report|\PHPUnit_Framework_MockObject_MockObject */
    private $report;

    /**
     * @test
     *
     * @dataProvider decisionsProvider
     */
    public function decisions($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDecisionsState()['state']);
    }

    /**
     * @return Report mock
     */
    private function getReportMocked(array $reportMethods = [], $hasBalance = true)
    {
        $report = m::mock(Report::class, $reportMethods + [
            'getSectionStatusesCached' => [],
            'getBankAccounts' => [],
            'getBankAccountsIncomplete' => [],
            'getExpenses' => [],
            'getPaidForAnything' => null,
            'expensesSectionCompleted' => null,
            'getGifts' => [],
            'giftsSectionCompleted' => null,
            'getMoneyTransfers' => [],
            'getNoTransfersToAdd' => null,
            'getAssets' => [],
            'getDecisions' => [],
            'getHasCapacityChanged' => null,
            'getNoAssetToAdd' => null,
            'getContacts' => [],
            'getReasonForNoContacts' => null,
            'getSignificantDecisionsMade' => null,
            'getReasonForNoDecisions' => null,
            'getVisitsCare' => m::mock(VisitsCare::class, [
                'getDoYouLiveWithClient' => null,
                'getDoesClientReceivePaidCare' => null,
                'getWhoIsDoingTheCaring' => null,
                'getDoesClientHaveACarePlan' => null,
            ]),
            'getLifestyle' => m::mock(VisitsCare::class, [
                'getCareAppointments' => null,
                'getDoesClientUndertakeSocialActivities' => null,
            ]),
            'getAction' => m::mock(Action::class, [
                'getDoYouExpectFinancialDecisions' => null,
                'getDoYouHaveConcerns' => null,
            ]),
            'getActionMoreInfo' => null,
            'getMentalCapacity' => null,
            'getMoneyInExists' => null,
            'hasMoneyIn' => false,
            'getMoneyTransactionsIn' => [],
            'getMoneyOutExists' => null,
            'hasMoneyOut' => false,
            'getMoneyTransactionsOut' => [],
            'getHasDebts' => null,
            'getDebts' => [],
            'getDebtsWithValidAmount' => [],
            'getDebtManagement' => null,
            'getTotalsMatch' => null,
            'getBalanceMismatchExplanation' => null,
            'getDocuments' => [],
            'getDeputyDocuments' => [],
            'getWishToProvideDocumentation' => null,
            // 103
            'getMoneyShortCategoriesIn' => [],
            'getMoneyShortCategoriesInPresent' => [],
            'getMoneyTransactionsShortInExist' => null,
            'getMoneyTransactionsShortIn' => [],
            'getMoneyShortCategoriesOut' => [],
            'getMoneyShortCategoriesOutPresent' => [],
            'getMoneyTransactionsShortOutExist' => null,
            'getMoneyTransactionsShortOut' => [],
            'getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
            // 106
            'has106Flag' => false,
            //                'getFeesWithValidAmount'                           => [],
            //                'getReasonForNoFees'                => null,
            'paFeesExpensesNotStarted' => null,
            'paFeesExpensesCompleted' => null,
            'getProfDeputyCostsHowCharged' => null,
            'hasProfDeputyCostsHowChargedFixedOnly' => null,
            'getProfDeputyCostsHasPrevious' => null,
            'getProfDeputyFixedCost' => null,
            'getProfDeputyCostsHasInterim' => null,
            'getProfDeputyCostsAmountToScco' => null,
            'hasProfDeputyOtherCosts' => null,
            'isMissingMoneyOrAccountsOrClosingBalance' => true,
            //                'hasSection' => false,
            // 'getExpenses'                       => [],
            // 'getPaidForAnything'                => null,
            'getAvailableSections' => [ // 102 sections
                'decisions', 'contacts', 'visitsCare', 'balance', 'bankAccounts',
                'moneyTransfers', 'moneyIn', 'moneyOut',
                'assets', 'debts', 'gifts', 'actions', 'otherInfo', 'deputyExpenses', ],
        ]);

        $report->shouldReceive('hasSection')->with('balance')->andReturn($hasBalance);

        return $report;
    }

    /**
     * @test
     *
     * @dataProvider contactsProvider
     */
    public function contacts($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getContactsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider visitsCareProvider
     */
    public function visitsCare($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getVisitsCareState()['state']);
    }

    public function lifestyleProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getCareAppointments' => null,
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getCareAppointments' => 'yes',
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getCareAppointments' => 'yes',
            'getDoesClientUndertakeSocialActivities' => 'yes',
        ]);

        return [
            [['getLifestyle' => $empty], StatusService::STATE_NOT_STARTED],
            [['getLifestyle' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getLifestyle' => $done], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider lifestyleProvider
     */
    public function lifestyle($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getLifestyleState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider bankAccountProvider
     */
    public function bankAccount($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getBankAccountsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider moneyTransferProvider
     */
    public function moneyTransfer($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyTransferState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider moneyInProvider
     */
    public function moneyIn($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyInState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider moneyOutProvider
     */
    public function moneyOut($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyOutState()['state']);
    }

    public function moneyInShortProvider()
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        return [
            [['getMoneyInExists' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyInExists' => 'No', 'getReasonForNoMoneyIn' => null], StatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes', 'getMoneyShortCategoriesInPresent' => [], 'getMoneyTransactionsShortInExist' => 'no'], StatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes', 'getMoneyShortCategoriesInPresent' => [], 'getMoneyTransactionsShortInExist' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes', 'getMoneyShortCategoriesInPresent' => [[$cat]], 'getMoneyTransactionsShortInExist' => 'no'], StatusService::STATE_LOW_ASSETS_DONE],
            [['getMoneyInExists' => 'Yes', 'getMoneyShortCategoriesInPresent' => [[$cat]], 'getMoneyTransactionsShortInExist' => 'yes', 'getMoneyTransactionsShortIn' => [$t]], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider moneyInShortProvider
     */
    public function moneyInShort($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyInShortState()['state']);
    }

    public function moneyOutShortProvider()
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        return [
            [['getMoneyOutExists' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyOutExists' => 'No', 'getReasonForNoMoneyOut' => null], StatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes', 'getMoneyShortCategoriesOutPresent' => [], 'getMoneyTransactionsShortOutExist' => 'no'], StatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes', 'getMoneyShortCategoriesOutPresent' => [], 'getMoneyTransactionsShortOutExist' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes', 'getMoneyShortCategoriesOutPresent' => [$cat], 'getMoneyTransactionsShortOutExist' => 'no'], StatusService::STATE_LOW_ASSETS_DONE],
            [['getMoneyOutExists' => 'Yes', 'getMoneyShortCategoriesOutPresent' => [[$cat]], 'getMoneyTransactionsShortOutExist' => 'yes', 'getMoneyTransactionsShortOut' => [$t]], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider moneyOutShortProvider
     */
    public function moneyOutShort($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyOutShortState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider expensesProvider
     */
    public function expenses($mocks, $state)
    {
        $report = $this->getReportMocked($mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);

        $object = new StatusService($report);
        $this->assertEquals($state, $object->getExpensesState()['state']);
    }

    public function paFeesExpensesProvider()
    {
        return [
            [['paFeesExpensesNotStarted' => true], StatusService::STATE_NOT_STARTED],
            [['paFeesExpensesNotStarted' => false, 'paFeesExpensesCompleted' => false], StatusService::STATE_INCOMPLETE],
            [['paFeesExpensesNotStarted' => false, 'paFeesExpensesCompleted' => true], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider paFeesExpensesProvider
     */
    public function paFeeExpenses($mocks, $state)
    {
        $report = $this->getReportMocked(['has106Flag' => true] + $mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(true);

        $object = new StatusService($report);
        $this->assertEquals($state, $object->getPaFeesExpensesState()['state']);
    }

    public function profDeputyCostsProvider()
    {
        $otherCostsSubmitted = [
            'hasProfDeputyOtherCosts' => true,
        ];
        $otherCostsNotSubmitted = [
            'hasProfDeputyOtherCosts' => false,
        ];
        $onlyFixedCosts = [
            'getProfDeputyCostsHowCharged' => 'fixed',
            'hasProfDeputyCostsHowChargedFixedOnly' => true,
        ];
        $bothFixedAndAssessed = [
            'getProfDeputyCostsHowCharged' => 'both',
            'hasProfDeputyCostsHowChargedFixedOnly' => false,
        ];

        $prevNo = ['getProfDeputyCostsHasPrevious' => 'no'];
        $prevYes = ['getProfDeputyCostsHasPrevious' => 'yes', 'getProfDeputyPreviousCosts' => [1, 2]];

        $interimNo = ['getProfDeputyCostsHasInterim' => 'no'];
        $interimYes = ['getProfDeputyCostsHasInterim' => 'yes', 'getProfDeputyInterimCosts' => [1, 2]];

        $fixed = ['getProfDeputyFixedCost' => 1];
        $scco = ['getProfDeputyCostsAmountToScco' => 1];

        return [
            [[], StatusService::STATE_NOT_STARTED], // no data at all

            [['getProfDeputyCostsHowCharged' => 'fixed'], StatusService::STATE_INCOMPLETE],
            [['getProfDeputyCostsHowCharged' => 'assessed'], StatusService::STATE_INCOMPLETE],
            [['getProfDeputyCostsHowCharged' => 'both'], StatusService::STATE_INCOMPLETE],

            // fixed costs: all flows
            [$onlyFixedCosts + $prevNo + $fixed + $scco + $otherCostsSubmitted, StatusService::STATE_DONE],
            [$onlyFixedCosts + $prevYes + $fixed + $scco + $otherCostsNotSubmitted, StatusService::STATE_INCOMPLETE],

            // same as above, but with some missing
            [$onlyFixedCosts + $interimNo + $fixed + $scco, StatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $interimNo + $fixed + $scco + $otherCostsSubmitted, StatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $prevNo + $scco, StatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $prevNo + $interimYes, StatusService::STATE_INCOMPLETE],

            // two ticked (equivalent to all ticked): all flows
            [$bothFixedAndAssessed + $prevNo + $interimYes + $scco + $otherCostsSubmitted, StatusService::STATE_DONE],
            [$bothFixedAndAssessed + $prevYes + $interimYes + $scco + $otherCostsSubmitted, StatusService::STATE_DONE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed + $scco + $otherCostsSubmitted, StatusService::STATE_DONE],

            [$bothFixedAndAssessed + $prevNo + $interimYes + $scco + $otherCostsNotSubmitted, StatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevYes + $interimYes + $scco + $otherCostsNotSubmitted, StatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed + $scco + $otherCostsNotSubmitted, StatusService::STATE_INCOMPLETE],

            // same as above, but with some missing
            [$bothFixedAndAssessed + $interimYes + $scco, StatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevYes + $scco, StatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $scco, StatusService::STATE_INCOMPLETE], // miss fixed
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed, StatusService::STATE_INCOMPLETE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider profDeputyCostsProvider
     */
    public function profDeputyCosts($mocks, $state)
    {
        $report = $this->getReportMocked([] + $mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PROF_DEPUTY_COSTS)->andReturn(true);

        $object = new StatusService($report);
        $this->assertEquals($state, $object->getProfDeputyCostsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider getProfDeputyCostsEstimateStateVariations
     */
    public function getProfDeputyCostsEstimateStateReturnsCurrentState($howCharged, $hasMoreInfo, $expectedStatus)
    {
        $this
            ->initReport()
            ->setProfDeputyCostsEstimateHowCharged($howCharged)
            ->setProfDeputyCostsEstimateHasMoreInfo($hasMoreInfo);

        $sut = new StatusService($this->report);
        $this->assertEquals($expectedStatus, $sut->getProfDeputyCostsEstimateState()['state']);
    }

    /**
     * @return $this
     */
    private function setProfDeputyCostsEstimateHasMoreInfo($value)
    {
        $this->report->setProfDeputyCostsEstimateHasMoreInfo($value);

        return $this;
    }

    /**
     * @return $this
     */
    private function setProfDeputyCostsEstimateHowCharged($value)
    {
        $this->report->setProfDeputyCostsEstimateHowCharged($value);

        return $this;
    }

    /**
     * @return $this
     */
    private function initReport()
    {
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime()])
            ->setMethods(['hasSection'])
            ->getMock();

        $this->report
            ->method('hasSection')
            ->with(Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE)
            ->willReturn(true);

        return $this;
    }

    /**
     * @return array
     */
    public function getProfDeputyCostsEstimateStateVariations()
    {
        return [
            [
                'howCharged' => null,
                'hasMoreInfo' => null,
                'expectedStatus' => StatusService::STATE_NOT_STARTED,
            ],
            [
                'howCharged' => 'fixed',
                'hasMoreInfo' => null,
                'expectedStatus' => StatusService::STATE_DONE,
            ],
            [
                'howCharged' => 'assessed',
                'hasMoreInfo' => null,
                'expectedStatus' => StatusService::STATE_INCOMPLETE,
            ],
            [
                'howCharged' => 'both',
                'hasMoreInfo' => null,
                'expectedStatus' => StatusService::STATE_INCOMPLETE,
            ],
            [
                'howCharged' => 'assessed',
                'hasMoreInfo' => 'yes',
                'expectedStatus' => StatusService::STATE_DONE,
            ],
            [
                'howCharged' => 'both',
                'hasMoreInfo' => 'yes',
                'expectedStatus' => StatusService::STATE_DONE,
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider giftsProvider
     */
    public function gifts($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getGiftsState()['state']);
    }

    /**
     * @dataProvider documentsProvider
     */
    public function testGetDocumentState($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDocumentsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider assetsProvider
     */
    public function assets($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getAssetsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider debtsProvider
     */
    public function debts($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDebtsState()['state']);
    }

    public function profCurrentFeesProvider()
    {
        $debt = m::mock(Debt::class);

        return [
            [['getCurrentProfPaymentsReceived' => null], StatusService::STATE_NOT_STARTED],
            [['getCurrentProfPaymentsReceived' => 'yes', 'profCurrentFeesSectionCompleted' => false], StatusService::STATE_INCOMPLETE],
            [['getCurrentProfPaymentsReceived' => 'yes', 'profCurrentFeesSectionCompleted' => true], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     *
     * @dataProvider profCurrentFeesProvider
     */
    public function profCurrentFeesState($mocks, $state)
    {
        $report = $this->getReportMocked($mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PROF_CURRENT_FEES)->andReturn(true);

        $object = new StatusService($report);
        $this->assertEquals($state, $object->getProfCurrentFeesState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider balanceProvider
     */
    public function balance($mocks, $state)
    {
        $report = $this->getReportMocked($mocks);
        // never happening with any report, but simpler to test them in a fake report type with both
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(true);

        $object = new StatusService($report);
        $this->assertEquals($state, $object->getBalanceState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider actionsProvider
     */
    public function actions($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getActionsState()['state']);
    }

    /**
     * @test
     *
     * @dataProvider otherInfoProvider
     */
    public function otherinfo($mocks, $state)
    {
        $object = new StatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getOtherInfoState()['state']);
    }

    //    public function testGetRemainingSectionsAndStatus()
    //    {
    //        $this->markTestSkipped('not easily testable after use of cache');
    //        $mocksCompletingReport = ['getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE]
    //            + array_pop($this->decisionsProvider())[0]
    //            + array_pop($this->contactsProvider())[0]
    //            + array_pop($this->visitsCareProvider())[0]
    //            + array_pop($this->actionsProvider())[0]
    //            + array_pop($this->otherInfoProvider())[0]
    //            + array_pop($this->giftsProvider())[0]
    //            + array_pop($this->documentsProvider())[0]
    //            + array_pop($this->balanceProvider())[0]
    //            + array_pop($this->bankAccountProvider())[0]
    //            + array_pop($this->expensesProvider())[0]
    //            + array_pop($this->assetsProvider())[0]
    //            + array_pop($this->debtsProvider())[0]
    //            + array_pop($this->moneyTransferProvider())[0]
    //            + array_pop($this->MoneyInProvider())[0]
    //            + array_pop($this->MoneyOutProvider())[0];
    //
    //        // all empty
    //        $report = $this->getReportMocked();
    //        $report->shouldReceive('isDue')->andReturn(true);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);
    //        $object = new StatusService($report);
    //        $this->assertNotEquals([], $object->getRemainingSections());
    //        $this->assertEquals('notStarted', $object->getStatus());
    //
    //        // due, half complete
    //        $dp = $this->decisionsProvider();
    //        $retPartial = ['getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE]
    //            + array_pop($dp)[0];
    //        $report = $this->getReportMocked($retPartial);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
    //        $object = new StatusService($report);
    //        $report->shouldReceive('isDue')->andReturn(true);
    //        $this->assertEquals('notFinished', $object->getStatus());
    //
    //        // not due, complete
    //        $report = $this->getReportMocked($mocksCompletingReport);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
    //        $object = new StatusService($report);
    //        $this->assertEquals([], $object->getRemainingSections());
    //        $report->shouldReceive('isDue')->andReturn(false);
    //        $this->assertEquals('notFinished', $object->getStatus());
    //
    //        // due, complete
    //        $report = $this->getReportMocked($mocksCompletingReport);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
    //        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
    //        $object = new StatusService($report);
    //        $report->shouldReceive('isDue')->andReturn(true);
    //        $this->assertEquals('readyToSubmit', $object->getStatus());
    //    }

    public function decisionsProvider()
    {
        $decision = m::mock(Decision::class);
        $mcEmpty = m::mock(MentalCapacity::class, [
            'getHasCapacityChanged' => null,
            'getMentalAssessmentDate' => null,
        ]);
        $mcPartial = m::mock(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => null,
        ]);
        $mcComplete = m::mock(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => new \DateTime('2016-01-01'),
        ]);

        return [
            [[], StatusService::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => [$decision]], StatusService::STATE_INCOMPLETE, false],
            [['getSignificantDecisionsMade' => 'No'], StatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcComplete], StatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcPartial, 'getDecisions' => [$decision]], StatusService::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mcComplete, 'getDecisions' => [$decision]], StatusService::STATE_DONE, true],
            [['getMentalCapacity' => $mcComplete, 'getReasonForNoDecisions' => 'x'], StatusService::STATE_DONE, true],
        ];
    }

    public function contactsProvider()
    {
        $contact = m::mock(Contact::class);

        return [
            [[], StatusService::STATE_NOT_STARTED, false],
            // done
            [['getContacts' => [$contact]], StatusService::STATE_DONE, true],
            [['getReasonForNoContacts' => 'x'], StatusService::STATE_DONE, true],
        ];
    }

    public function visitsCareProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => null,
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientReceivePaidCare' => 'yes',
            'getWhoIsDoingTheCaring' => 'xxx',
            'getDoesClientHaveACarePlan' => 'yes',
        ]);

        return [
            [['getVisitsCare' => $empty], StatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getVisitsCare' => $done], StatusService::STATE_DONE],
        ];
    }

    public function actionsProvider()
    {
        $empty = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => null,
            'getDoYouHaveConcerns' => null,
        ]);

        $incomplete = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => 'yes',
            'getDoYouHaveConcerns' => null,
        ]);

        $done = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => 'yes',
            'getDoYouHaveConcerns' => 'no',
        ]);

        return [
            [['getAction' => $empty], StatusService::STATE_NOT_STARTED],
            [['getAction' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getAction' => $done], StatusService::STATE_DONE],
        ];
    }

    public function otherInfoProvider()
    {
        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getActionMoreInfo' => 'mr'], StatusService::STATE_DONE],
        ];
    }

    public function giftsProvider()
    {
        return [
            [['giftsSectionCompleted' => false], StatusService::STATE_NOT_STARTED],
            [['giftsSectionCompleted' => true], StatusService::STATE_DONE],
        ];
    }

    public function documentsProvider()
    {
        $document = m::mock(Document::class);

        return [
            [['getWishToProvideDocumentation' => 'no'], StatusService::STATE_DONE],
            [['getDocuments' => []], StatusService::STATE_NOT_STARTED],
            [['getWishToProvideDocumentation' => 'yes', 'getDeputyDocuments' => []], StatusService::STATE_INCOMPLETE],
            [['getWishToProvideDocumentation' => 'yes', 'getDeputyDocuments' => [$document]], StatusService::STATE_DONE],
        ];
    }

    public function balanceProvider()
    {
        // if any of the dependend section is not completed, status should be not-started
        $allComplete = [
            'isMissingMoneyOrAccountsOrClosingBalance' => false,
            'hasMoneyIn' => true,
            'hasMoneyOut' => true,
            'giftsSectionCompleted' => true,
            'expensesSectionCompleted' => true,
            'paFeesExpensesNotStarted' => false,
            'paFeesExpensesCompleted' => true,
        ];
        $banksNotCompleted = ['isMissingMoneyOrAccountsOrClosingBalance' => true] + $allComplete;
        $giftsNotCompleted = ['giftsSectionCompleted' => false] + $allComplete;
        $deputyExpensesNotCompleted = ['expensesSectionCompleted' => false] + $allComplete;
        $paFeesExpensesNotCompleted = ['paFeesExpensesCompleted' => false] + $allComplete;

        return [
            [$banksNotCompleted, StatusService::STATE_NOT_STARTED],
            [$giftsNotCompleted, StatusService::STATE_NOT_STARTED],
            [$deputyExpensesNotCompleted, StatusService::STATE_NOT_STARTED],
            [$paFeesExpensesNotCompleted, StatusService::STATE_NOT_STARTED],
            [$allComplete + ['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => ''], StatusService::STATE_NOT_MATCHING],
            [$allComplete + ['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => 'reason'], StatusService::STATE_EXPLAINED],
            [$allComplete + ['getTotalsMatch' => true], StatusService::STATE_DONE],
        ];
    }

    public function bankAccountProvider()
    {
        $account = m::mock(Account::class);

        return [
            [['getBankAccounts' => [], 'getBankAccountsIncomplete' => []], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$account], 'getBankAccountsIncomplete' => [$account]], StatusService::STATE_INCOMPLETE],
            [['getBankAccounts' => [$account], 'getBankAccountsIncomplete' => []], StatusService::STATE_DONE],
        ];
    }

    public function expensesProvider()
    {
        $expense = m::mock(Expense::class);

        return [
            [['expensesSectionCompleted' => false], StatusService::STATE_NOT_STARTED],
            [['expensesSectionCompleted' => true], StatusService::STATE_DONE],
        ];
    }

    public function assetsProvider()
    {
        $asset = m::mock(Asset::class);

        return [
            [['getAssets' => [], 'getNoAssetToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => null], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
            [['getAssets' => [], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
        ];
    }

    public function debtsProvider()
    {
        $debt = m::mock(Debt::class);

        return [
            [['getHasDebts' => false], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt]], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => ''], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => 'Payment plan'], StatusService::STATE_DONE],
            [['getHasDebts' => 'no'], StatusService::STATE_DONE],
        ];
    }

    public function moneyTransferProvider()
    {
        $account1 = m::mock(Account::class);
        $account2 = m::mock(Account::class);
        $mt1 = m::mock(MoneyTransfer::class);

        return [
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [$mt1], 'getNoTransfersToAdd' => null], StatusService::STATE_DONE],
            [['getBankAccounts' => [$account1, $account2], 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => true], StatusService::STATE_DONE],
            // less than 2 accounts => done
            [['getBankAccounts' => []], StatusService::STATE_DONE],
            [['getBankAccounts' => [$account1]], StatusService::STATE_DONE],
        ];
    }

    public function moneyInProvider()
    {
        return [
            [['hasMoneyIn' => false], StatusService::STATE_NOT_STARTED],
            [['hasMoneyIn' => true], StatusService::STATE_DONE],
        ];
    }

    public function moneyOutProvider()
    {
        return [
            [['hasMoneyOut' => false], StatusService::STATE_NOT_STARTED],
            [['hasMoneyOut' => true], StatusService::STATE_DONE],
        ];
    }

    public function testGetStatusReadyToSubmit()
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $report->isDue()->shouldBeCalled()->willReturn(true);

        $sut = new StatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }

    public function testGetStatusNotFinished()
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $report->isDue()->shouldBeCalled()->willReturn(false);

        $sut = new StatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_FINISHED, $status);
    }

    public function testGetStatusNotStarted()
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(false);

        $sut = new StatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_STARTED, $status);
    }

    public function testGetStatusIgnoringDueDateReadyToSubmit()
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $sut = new StatusService($report->reveal());
        $status = $sut->getStatusIgnoringDueDate();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }
}

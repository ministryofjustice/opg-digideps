<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Action;
use OPG\Digideps\Backend\Entity\Report\Asset;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Debt;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\MentalCapacity;
use OPG\Digideps\Backend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShort;
use OPG\Digideps\Backend\Entity\Report\MoneyTransfer;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\VisitsCare;
use OPG\Digideps\Backend\Service\ReportStatusService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ReportStatusServiceTest extends TestCase
{
    use ProphecyTrait;

    private Report&MockObject $report;

    #[DataProvider('decisionsProvider')]
    #[Test]
    public function decisions(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDecisionsState()['state']);
    }

    private function getReportMocked(array $reportMethods = [], $hasBalance = true): Report
    {
        $report = m::mock(Report::class, $reportMethods + [
                'getSectionStatusesCached' => [],
                'getBankAccounts' => new ArrayCollection([]),
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
                'getMoneyTransactionsIn' => new ArrayCollection([]),
                'getMoneyOutExists' => null,
                'hasMoneyOut' => false,
                'getMoneyTransactionsOut' => new ArrayCollection([]),
                'getHasDebts' => null,
                'getDebts' => [],
                'getDebtsWithValidAmount' => [],
                'getDebtManagement' => null,
                'getTotalsMatch' => null,
                'getBalanceMismatchExplanation' => null,
                'getDocuments' => [],
                'getDeputyDocuments' => new ArrayCollection([]),
                'getWishToProvideDocumentation' => null,
                // 103
                'getMoneyShortCategoriesIn' => [],
                'getMoneyShortCategoriesInPresent' => new ArrayCollection([]),
                'getMoneyTransactionsShortInExist' => null,
                'getMoneyTransactionsShortIn' => new ArrayCollection([]),
                'getMoneyShortCategoriesOut' => new ArrayCollection([]),
                'getMoneyShortCategoriesOutPresent' => new ArrayCollection([]),
                'getMoneyTransactionsShortOutExist' => null,
                'getMoneyTransactionsShortOut' => new ArrayCollection([]),
                'getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE,
                // 106
                'has106Flag' => false,
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
                'getAvailableSections' => [ // 102 sections
                    'decisions', 'contacts', 'visitsCare', 'balance', 'bankAccounts',
                    'moneyTransfers', 'moneyIn', 'moneyOut',
                    'assets', 'debts', 'gifts', 'actions', 'otherInfo', 'deputyExpenses', ],
            ]);

        $report->shouldReceive('hasSection')->with('balance')->andReturn($hasBalance);

        return $report;
    }

    #[DataProvider('contactsProvider')]
    #[Test]
    public function contacts(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getContactsState()['state']);
    }

    #[DataProvider('visitsCareProvider')]
    #[Test]
    public function visitsCare(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getVisitsCareState()['state']);
    }

    public static function lifestyleProvider(): array
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
            [['getLifestyle' => $empty], ReportStatusService::STATE_NOT_STARTED],
            [['getLifestyle' => $incomplete], ReportStatusService::STATE_INCOMPLETE],
            [['getLifestyle' => $done], ReportStatusService::STATE_DONE],
        ];
    }

    #[DataProvider('lifestyleProvider')]
    #[Test]
    public function lifestyle(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getLifestyleState()['state']);
    }

    #[DataProvider('bankAccountProvider')]
    #[Test]
    public function bankAccount(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getBankAccountsState()['state']);
    }

    #[DataProvider('moneyTransferProvider')]
    #[Test]
    public function moneyTransfer(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyTransferState()['state']);
    }

    #[DataProvider('moneyInProvider')]
    #[Test]
    public function moneyIn(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyInState()['state']);
    }

    #[DataProvider('moneyOutProvider')]
    #[Test]
    public function moneyOut(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyOutState()['state']);
    }

    public static function moneyInShortProvider(): array
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        $oneCategory = new ArrayCollection([$cat]);
        $emptyCategories = new ArrayCollection([]);
        $oneTransaction = new ArrayCollection([$t]);

        return [
            [['getMoneyInExists' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getMoneyInExists' => 'No','getReasonForNoMoneyIn' => null], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes','getMoneyShortCategoriesInPresent' => $emptyCategories,'getMoneyTransactionsShortInExist' => 'no'], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes','getMoneyShortCategoriesInPresent' => $emptyCategories,'getMoneyTransactionsShortInExist' => 'yes'], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyInExists' => 'Yes','getMoneyShortCategoriesInPresent' => $oneCategory,'getMoneyTransactionsShortInExist' => 'no'], ReportStatusService::STATE_LOW_ASSETS_DONE],
            [['getMoneyInExists' => 'Yes','getMoneyShortCategoriesInPresent' => $oneCategory,'getMoneyTransactionsShortInExist' => 'yes', 'getMoneyTransactionsShortIn' => $oneTransaction], ReportStatusService::STATE_DONE],
        ];
    }

    #[DataProvider('moneyInShortProvider')]
    #[Test]
    public function moneyInShort(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyInShortState()['state']);
    }

    public static function moneyOutShortProvider(): array
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        $emptyCategories = new ArrayCollection([]);
        $oneCategory = new ArrayCollection([$cat]);
        $oneTransaction = new ArrayCollection([$t]);

        return [
            [['getMoneyOutExists' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getMoneyOutExists' => 'No','getReasonForNoMoneyOut' => null], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes','getMoneyShortCategoriesOutPresent' => $emptyCategories,'getMoneyTransactionsShortOutExist' => 'no'], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes','getMoneyShortCategoriesOutPresent' => $emptyCategories,'getMoneyTransactionsShortOutExist' => 'yes'], ReportStatusService::STATE_INCOMPLETE],
            [['getMoneyOutExists' => 'Yes','getMoneyShortCategoriesOutPresent' => $oneCategory,'getMoneyTransactionsShortOutExist' => 'no'], ReportStatusService::STATE_LOW_ASSETS_DONE],
            [['getMoneyOutExists' => 'Yes','getMoneyShortCategoriesOutPresent' => $oneCategory,'getMoneyTransactionsShortOutExist' => 'yes', 'getMoneyTransactionsShortOut' => $oneTransaction], ReportStatusService::STATE_DONE],
        ];
    }

    #[DataProvider('moneyOutShortProvider')]
    #[Test]
    public function moneyOutShort(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getMoneyOutShortState()['state']);
    }

    #[DataProvider('expensesProvider')]
    #[Test]
    public function expenses(array $mocks, string $state): void
    {
        $report = $this->getReportMocked($mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);

        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getExpensesState()['state']);
    }

    public static function paFeesExpensesProvider(): array
    {
        return [
            [['paFeesExpensesNotStarted' => true], ReportStatusService::STATE_NOT_STARTED],
            [['paFeesExpensesNotStarted' => false, 'paFeesExpensesCompleted' => false], ReportStatusService::STATE_INCOMPLETE],
            [['paFeesExpensesNotStarted' => false, 'paFeesExpensesCompleted' => true], ReportStatusService::STATE_DONE],
        ];
    }

    #[DataProvider('paFeesExpensesProvider')]
    #[Test]
    public function paFeeExpenses(array $mocks, string $state): void
    {
        $report = $this->getReportMocked(['has106Flag' => true] + $mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(true);

        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getPaFeesExpensesState()['state']);
    }

    public static function profDeputyCostsProvider(): array
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
            [[], ReportStatusService::STATE_NOT_STARTED], // no data at all

            [['getProfDeputyCostsHowCharged' => 'fixed'], ReportStatusService::STATE_INCOMPLETE],
            [['getProfDeputyCostsHowCharged' => 'assessed'], ReportStatusService::STATE_INCOMPLETE],
            [['getProfDeputyCostsHowCharged' => 'both'], ReportStatusService::STATE_INCOMPLETE],

            // fixed costs: all flows
            [$onlyFixedCosts + $prevNo + $fixed + $scco + $otherCostsSubmitted, ReportStatusService::STATE_DONE],
            [$onlyFixedCosts + $prevYes + $fixed + $scco + $otherCostsNotSubmitted, ReportStatusService::STATE_INCOMPLETE],

            // same as above, but with some missing
            [$onlyFixedCosts + $interimNo + $fixed + $scco, ReportStatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $interimNo + $fixed + $scco + $otherCostsSubmitted, ReportStatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $prevNo + $scco, ReportStatusService::STATE_INCOMPLETE],
            [$onlyFixedCosts + $prevNo + $interimYes, ReportStatusService::STATE_INCOMPLETE],

            // two ticked (equivalent to all ticked): all flows
            [$bothFixedAndAssessed + $prevNo + $interimYes + $scco + $otherCostsSubmitted, ReportStatusService::STATE_DONE],
            [$bothFixedAndAssessed + $prevYes + $interimYes + $scco + $otherCostsSubmitted, ReportStatusService::STATE_DONE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed + $scco + $otherCostsSubmitted, ReportStatusService::STATE_DONE],

            [$bothFixedAndAssessed + $prevNo + $interimYes + $scco + $otherCostsNotSubmitted, ReportStatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevYes + $interimYes + $scco + $otherCostsNotSubmitted, ReportStatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed + $scco + $otherCostsNotSubmitted, ReportStatusService::STATE_INCOMPLETE],

            // same as above, but with some missing
            [$bothFixedAndAssessed + $interimYes + $scco, ReportStatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevYes + $scco, ReportStatusService::STATE_INCOMPLETE],
            [$bothFixedAndAssessed + $prevNo + $interimNo + $scco, ReportStatusService::STATE_INCOMPLETE], // miss fixed
            [$bothFixedAndAssessed + $prevNo + $interimNo + $fixed, ReportStatusService::STATE_INCOMPLETE],
        ];
    }

    #[DataProvider('profDeputyCostsProvider')]
    #[Test]
    public function profDeputyCosts(array $mocks, string $state): void
    {
        $report = $this->getReportMocked([] + $mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PROF_DEPUTY_COSTS)->andReturn(true);

        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getProfDeputyCostsState()['state']);
    }

    #[DataProvider('getProfDeputyCostsEstimateStateVariations')]
    #[Test]
    public function getProfDeputyCostsEstimateStateReturnsCurrentState(?string $howCharged, ?string $hasMoreInfo, string $expectedStatus): void
    {
        $this
            ->initReport()
            ->setProfDeputyCostsEstimateHowCharged($howCharged)
            ->setProfDeputyCostsEstimateHasMoreInfo($hasMoreInfo);

        $sut = new ReportStatusService($this->report);
        $this->assertEquals($expectedStatus, $sut->getProfDeputyCostsEstimateState()['state']);
    }

    private function setProfDeputyCostsEstimateHasMoreInfo(?string $value): static
    {
        $this->report->setProfDeputyCostsEstimateHasMoreInfo($value);

        return $this;
    }

    private function setProfDeputyCostsEstimateHowCharged(?string $value): static
    {
        $this->report->setProfDeputyCostsEstimateHowCharged($value);

        return $this;
    }

    private function initReport(): static
    {
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, new \DateTime(), new \DateTime()])
            ->onlyMethods(['hasSection'])
            ->getMock();

        $this->report
            ->method('hasSection')
            ->with(Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE)
            ->willReturn(true);

        return $this;
    }

    public static function getProfDeputyCostsEstimateStateVariations(): array
    {
        return [
            [
                'howCharged' => null,
                'hasMoreInfo' => null,
                'expectedStatus' => ReportStatusService::STATE_NOT_STARTED,
            ],
            [
                'howCharged' => 'fixed',
                'hasMoreInfo' => null,
                'expectedStatus' => ReportStatusService::STATE_DONE,
            ],
            [
                'howCharged' => 'assessed',
                'hasMoreInfo' => null,
                'expectedStatus' => ReportStatusService::STATE_INCOMPLETE,
            ],
            [
                'howCharged' => 'both',
                'hasMoreInfo' => null,
                'expectedStatus' => ReportStatusService::STATE_INCOMPLETE,
            ],
            [
                'howCharged' => 'assessed',
                'hasMoreInfo' => 'yes',
                'expectedStatus' => ReportStatusService::STATE_DONE,
            ],
            [
                'howCharged' => 'both',
                'hasMoreInfo' => 'yes',
                'expectedStatus' => ReportStatusService::STATE_DONE,
            ],
        ];
    }

    #[DataProvider('giftsProvider')]
    #[Test]
    public function gifts(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getGiftsState()['state']);
    }

    #[DataProvider('documentsProvider')]
    public function testGetDocumentState(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDocumentsState()['state']);
    }

    #[DataProvider('assetsProvider')]
    #[Test]
    public function assets(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getAssetsState()['state']);
    }

    #[DataProvider('debtsProvider')]
    #[Test]
    public function debts(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDebtsState()['state']);
    }

    public static function profCurrentFeesProvider(): array
    {
        $debt = m::mock(Debt::class);

        return [
            [['getCurrentProfPaymentsReceived' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getCurrentProfPaymentsReceived' => 'yes', 'profCurrentFeesSectionCompleted' => false], ReportStatusService::STATE_INCOMPLETE],
            [['getCurrentProfPaymentsReceived' => 'yes', 'profCurrentFeesSectionCompleted' => true], ReportStatusService::STATE_DONE],
        ];
    }

    #[DataProvider('profCurrentFeesProvider')]
    #[Test]
    public function profCurrentFeesState(array $mocks, string $state): void
    {
        $report = $this->getReportMocked($mocks);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PROF_CURRENT_FEES)->andReturn(true);

        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getProfCurrentFeesState()['state']);
    }

    #[DataProvider('balanceProvider')]
    #[Test]
    public function balance(array $mocks, string $state): void
    {
        $report = $this->getReportMocked($mocks);
        // never happening with any report, but simpler to test them in a fake report type with both
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(true);

        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getBalanceState()['state']);
    }

    #[DataProvider('actionsProvider')]
    #[Test]
    public function actions(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getActionsState()['state']);
    }

    #[DataProvider('otherInfoProvider')]
    #[Test]
    public function otherinfo(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getOtherInfoState()['state']);
    }

    public function testGetRemainingSectionsAndStatus(): void
    {
        $this->markTestSkipped('not easily testable after use of cache');
        $mocksCompletingReport = ['getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE]
            + array_pop($this->decisionsProvider())[0]
            + array_pop($this->contactsProvider())[0]
            + array_pop($this->visitsCareProvider())[0]
            + array_pop($this->actionsProvider())[0]
            + array_pop($this->otherInfoProvider())[0]
            + array_pop($this->giftsProvider())[0]
            + array_pop($this->documentsProvider())[0]
            + array_pop($this->balanceProvider())[0]
            + array_pop($this->bankAccountProvider())[0]
            + array_pop($this->expensesProvider())[0]
            + array_pop($this->assetsProvider())[0]
            + array_pop($this->debtsProvider())[0]
            + array_pop($this->moneyTransferProvider())[0]
            + array_pop($this->MoneyInProvider())[0]
            + array_pop($this->MoneyOutProvider())[0];

        // all empty
        $report = $this->getReportMocked();
        $report->shouldReceive('isDue')->andReturn(true);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(true);
        $object = new ReportStatusService($report);
        $this->assertNotEquals([], $object->getRemainingSections());
        $this->assertEquals('notStarted', $object->getStatus());

        // due, half complete
        $dp = $this->decisionsProvider();
        $retPartial = ['getType' => Report::LAY_PFA_HIGH_ASSETS_TYPE]
            + array_pop($dp)[0];
        $report = $this->getReportMocked($retPartial);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
        $object = new ReportStatusService($report);
        $report->shouldReceive('isDue')->andReturn(true);
        $this->assertEquals('notFinished', $object->getStatus());

        // not due, complete
        $report = $this->getReportMocked($mocksCompletingReport);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
        $object = new ReportStatusService($report);
        $this->assertEquals([], $object->getRemainingSections());
        $report->shouldReceive('isDue')->andReturn(false);
        $this->assertEquals('notFinished', $object->getStatus());

        // due, complete
        $report = $this->getReportMocked($mocksCompletingReport);
        $report->shouldReceive('hasSection')->with(Report::SECTION_DEPUTY_EXPENSES)->andReturn(false);
        $report->shouldReceive('hasSection')->with(Report::SECTION_PA_DEPUTY_EXPENSES)->andReturn(false);
        $object = new ReportStatusService($report);
        $report->shouldReceive('isDue')->andReturn(true);
        $this->assertEquals('readyToSubmit', $object->getStatus());
    }

    public static function decisionsProvider(): array
    {
        $decision = m::mock(Decision::class);
        $mcPartial = m::mock(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => null,
        ]);
        $mcComplete = m::mock(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => new \DateTime('2016-01-01'),
        ]);

        return [
            [[], ReportStatusService::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => [$decision]], ReportStatusService::STATE_INCOMPLETE, false],
            [['getSignificantDecisionsMade' => 'No'], ReportStatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcComplete], ReportStatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcPartial, 'getDecisions' => [$decision]], ReportStatusService::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mcComplete, 'getDecisions' => [$decision]], ReportStatusService::STATE_DONE, true],
            [['getMentalCapacity' => $mcComplete, 'getReasonForNoDecisions' => 'x'], ReportStatusService::STATE_DONE, true],
        ];
    }

    public static function contactsProvider(): array
    {
        $contact = m::mock(Contact::class);

        return [
            [[], ReportStatusService::STATE_NOT_STARTED, false],
            // done
            [['getContacts' => [$contact]], ReportStatusService::STATE_DONE, true],
            [['getReasonForNoContacts' => 'x'], ReportStatusService::STATE_DONE, true],
        ];
    }

    public static function visitsCareProvider(): array
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
            [['getVisitsCare' => $empty], ReportStatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $incomplete], ReportStatusService::STATE_INCOMPLETE],
            [['getVisitsCare' => $done], ReportStatusService::STATE_DONE],
        ];
    }

    public static function actionsProvider(): array
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
            [['getAction' => $empty], ReportStatusService::STATE_NOT_STARTED],
            [['getAction' => $incomplete], ReportStatusService::STATE_INCOMPLETE],
            [['getAction' => $done], ReportStatusService::STATE_DONE],
        ];
    }

    public static function otherInfoProvider(): array
    {
        return [
            [[], ReportStatusService::STATE_NOT_STARTED],
            [['getActionMoreInfo' => 'mr'], ReportStatusService::STATE_DONE],
        ];
    }

    public static function giftsProvider(): array
    {
        return [
            [['giftsSectionCompleted' => false], ReportStatusService::STATE_NOT_STARTED],
            [['giftsSectionCompleted' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function documentsProvider(): array
    {
        $document = m::mock(Document::class);

        return [
            [['getWishToProvideDocumentation' => 'no'], ReportStatusService::STATE_DONE],
            [['getDocuments' => []], ReportStatusService::STATE_NOT_STARTED],
            [['getWishToProvideDocumentation' => 'yes', 'getDeputyDocuments' => new ArrayCollection([])], ReportStatusService::STATE_INCOMPLETE],
            [['getWishToProvideDocumentation' => 'yes', 'getDeputyDocuments' => new ArrayCollection([$document])], ReportStatusService::STATE_DONE],
        ];
    }

    public static function balanceProvider(): array
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
            [$banksNotCompleted, ReportStatusService::STATE_NOT_STARTED],
            [$giftsNotCompleted, ReportStatusService::STATE_NOT_STARTED],
            [$deputyExpensesNotCompleted, ReportStatusService::STATE_NOT_STARTED],
            [$paFeesExpensesNotCompleted, ReportStatusService::STATE_NOT_STARTED],
            [$allComplete + ['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => ''], ReportStatusService::STATE_NOT_MATCHING],
            [$allComplete + ['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => 'reason'], ReportStatusService::STATE_EXPLAINED],
            [$allComplete + ['getTotalsMatch' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function bankAccountProvider(): array
    {
        $accounts = new ArrayCollection([m::mock(BankAccount::class)]);
        $emptyAccounts = new ArrayCollection([]);

        return [
            [['getBankAccounts' => $emptyAccounts, 'getBankAccountsIncomplete' => []], ReportStatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => $accounts, 'getBankAccountsIncomplete' => $accounts], ReportStatusService::STATE_INCOMPLETE],
            [['getBankAccounts' => $accounts, 'getBankAccountsIncomplete' => $emptyAccounts], ReportStatusService::STATE_DONE],
        ];
    }

    public static function expensesProvider(): array
    {
        return [
            [['expensesSectionCompleted' => false], ReportStatusService::STATE_NOT_STARTED],
            [['expensesSectionCompleted' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function assetsProvider(): array
    {
        $asset = m::mock(Asset::class);

        return [
            [['getAssets' => [], 'getNoAssetToAdd' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => null], ReportStatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => true], ReportStatusService::STATE_DONE],
            [['getAssets' => [], 'getNoAssetToAdd' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function debtsProvider(): array
    {
        $debt = m::mock(Debt::class);

        return [
            [['getHasDebts' => false], ReportStatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt]], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => ''], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => 'Payment plan'], ReportStatusService::STATE_DONE],
            [['getHasDebts' => 'no'], ReportStatusService::STATE_DONE],
        ];
    }

    public static function moneyTransferProvider(): array
    {
        $account1 = m::mock(BankAccount::class);
        $account2 = m::mock(BankAccount::class);
        $mt1 = m::mock(MoneyTransfer::class);

        $bothAccounts = new ArrayCollection([$account1, $account2]);
        $noAccounts = new ArrayCollection([]);

        return [
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => [$mt1], 'getNoTransfersToAdd' => null], ReportStatusService::STATE_DONE],
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => [], 'getNoTransfersToAdd' => true], ReportStatusService::STATE_DONE],
            // less than 2 accounts => done
            [['getBankAccounts' => $noAccounts], ReportStatusService::STATE_DONE],
            [['getBankAccounts' => new ArrayCollection([$account1])], ReportStatusService::STATE_DONE],
        ];
    }

    public static function moneyInProvider(): array
    {
        return [
            [['hasMoneyIn' => false], ReportStatusService::STATE_NOT_STARTED],
            [['hasMoneyIn' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function moneyOutProvider(): array
    {
        return [
            [['hasMoneyOut' => false], ReportStatusService::STATE_NOT_STARTED],
            [['hasMoneyOut' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public function testGetStatusReadyToSubmit(): void
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $report->isDue()->shouldBeCalled()->willReturn(true);

        $sut = new ReportStatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }

    public function testGetStatusNotFinished(): void
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $report->isDue()->shouldBeCalled()->willReturn(false);

        $sut = new ReportStatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_FINISHED, $status);
    }

    public function testGetStatusNotStarted(): void
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(false);

        $sut = new ReportStatusService($report->reveal());
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_STARTED, $status);
    }

    public function testGetStatusIgnoringDueDateReadyToSubmit(): void
    {
        $report = $this->prophesize(Report::class);

        $report->getAvailableSections()->shouldBeCalled()->willReturn([Report::SECTION_GIFTS]);
        $report->getSectionStatusesCached()->shouldBeCalled()->willReturn([]);
        $report->giftsSectionCompleted()->shouldBeCalled()->willReturn(true);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);

        $sut = new ReportStatusService($report->reveal());
        $status = $sut->getStatusIgnoringDueDate();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }
}

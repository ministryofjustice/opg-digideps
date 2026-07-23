<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Action;
use OPG\Digideps\Backend\Entity\Report\Asset;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Debt;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Lifestyle;
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

final class ReportStatusServiceTest extends TestCase
{
    private Report&MockObject $report;

    #[DataProvider('decisionsProvider')]
    #[Test]
    public function decisions(array $mocks, string $state): void
    {
        $object = new ReportStatusService($this->getReportMocked($mocks));
        $this->assertEquals($state, $object->getDecisionsState()['state']);
    }

    private function getReportMocked(array $reportMethods = []): Report&MockObject
    {
        $report = self::createConfiguredMock(
            Report::class,
            $reportMethods + [
            'getSectionStatusesCached' => [],
            'getBankAccounts' => new ArrayCollection([]),
            'getBankAccountsIncomplete' => new ArrayCollection([]),
            'getExpenses' => new ArrayCollection([]),
            'getPaidForAnything' => null,
            'expensesSectionCompleted' => false,
            'getGifts' => new ArrayCollection([]),
            'giftsSectionCompleted' => false,
            'getMoneyTransfers' => new ArrayCollection([]),
            'getNoTransfersToAdd' => null,
            'getAssets' => new ArrayCollection([]),
            'getDecisions' => new ArrayCollection([]),
            'getNoAssetToAdd' => null,
            'getContacts' => new ArrayCollection([]),
            'getReasonForNoContacts' => null,
            'getSignificantDecisionsMade' => null,
            'getReasonForNoDecisions' => null,
            'getVisitsCare' => self::createConfiguredMock(VisitsCare::class, [
                'getDoYouLiveWithClient' => null,
                'getDoesClientReceivePaidCare' => null,
                'getWhoIsDoingTheCaring' => null,
                'getDoesClientHaveACarePlan' => null,
            ]),
            'getLifestyle' => self::createConfiguredMock(Lifestyle::class, [
                'getCareAppointments' => null,
                'getDoesClientUndertakeSocialActivities' => null,
            ]),
            'getAction' => self::createConfiguredMock(Action::class, [
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
            'getDebts' => new ArrayCollection([]),
            'getDebtsWithValidAmount' => new ArrayCollection([]),
            'getDebtManagement' => null,
            'getTotalsMatch' => false,
            'getBalanceMismatchExplanation' => null,
            'getDocuments' => new ArrayCollection([]),
            'getDeputyDocuments' => new ArrayCollection([]),
            'getWishToProvideDocumentation' => null,
            // 103
            'getMoneyShortCategoriesIn' => new ArrayCollection([]),
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
            'paFeesExpensesNotStarted' => false,
            'paFeesExpensesCompleted' => false,
            'getProfDeputyCostsHowCharged' => null,
            'hasProfDeputyCostsHowChargedFixedOnly' => false,
            'getProfDeputyCostsHasPrevious' => null,
            'getProfDeputyFixedCost' => 'no',
            'getProfDeputyCostsHasInterim' => 'no',
            'getProfDeputyCostsAmountToScco' => '0',
            'hasProfDeputyOtherCosts' => false,
            'isMissingMoneyOrAccountsOrClosingBalance' => true,
            'getAvailableSections' => [ // 102 sections
                'decisions', 'contacts', 'visitsCare', 'balance', 'bankAccounts',
                'moneyTransfers', 'moneyIn', 'moneyOut',
                'assets', 'debts', 'gifts', 'actions', 'otherInfo', 'deputyExpenses', ],
            ]
        );

        $report->method('hasSection')->willReturn(true);

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
        $empty = self::createConfiguredStub(Lifestyle::class, [
            'getCareAppointments' => null,
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $incomplete = self::createConfiguredStub(Lifestyle::class, [
            'getCareAppointments' => 'yes',
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $done = self::createConfiguredStub(Lifestyle::class, [
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
        $cat = self::createStub(MoneyShortCategory::class);
        $t = self::createStub(MoneyTransactionShort::class);

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
        $cat = self::createStub(MoneyShortCategory::class);
        $t = self::createStub(MoneyTransactionShort::class);

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
        $prevYes = ['getProfDeputyCostsHasPrevious' => 'yes', 'getProfDeputyPreviousCosts' => new ArrayCollection([1, 2])];

        $interimNo = ['getProfDeputyCostsHasInterim' => 'no'];
        $interimYes = ['getProfDeputyCostsHasInterim' => 'yes', 'getProfDeputyInterimCosts' => new ArrayCollection([1, 2])];

        $fixed = ['getProfDeputyFixedCost' => 'yes'];
        $scco = ['getProfDeputyCostsAmountToScco' => '1'];

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
        $object = new ReportStatusService($report);
        $this->assertEquals($state, $object->getProfDeputyCostsState()['state']);
    }

    #[DataProvider('getProfDeputyCostsEstimateStateVariations')]
    #[Test]
    public function getProfDeputyCostsEstimateStateReturnsCurrentState(?string $howCharged, ?string $hasMoreInfo, string $expectedStatus): void
    {
        $this->initReport()
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

    #[DataProvider('balanceProvider')]
    #[Test]
    public function balance(array $mocks, string $state): void
    {
        $report = $this->getReportMocked($mocks);
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

    public static function decisionsProvider(): array
    {
        $decision = self::createStub(Decision::class);
        $mcPartial = self::createConfiguredStub(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => null,
        ]);
        $mcComplete = self::createConfiguredStub(MentalCapacity::class, [
            'getHasCapacityChanged' => 'no',
            'getMentalAssessmentDate' => new \DateTime('2016-01-01'),
        ]);

        return [
            [[], ReportStatusService::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => new ArrayCollection([$decision])], ReportStatusService::STATE_INCOMPLETE, false],
            [['getSignificantDecisionsMade' => 'No'], ReportStatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcComplete], ReportStatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcPartial, 'getDecisions' => new ArrayCollection([$decision])], ReportStatusService::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mcComplete, 'getDecisions' => new ArrayCollection([$decision])], ReportStatusService::STATE_DONE, true],
            [['getMentalCapacity' => $mcComplete, 'getReasonForNoDecisions' => 'x'], ReportStatusService::STATE_DONE, true],
        ];
    }

    public static function contactsProvider(): array
    {
        $contact = self::createStub(Contact::class);

        return [
            [[], ReportStatusService::STATE_NOT_STARTED, false],
            // done
            [['getContacts' => new ArrayCollection([$contact])], ReportStatusService::STATE_DONE, true],
            [['getReasonForNoContacts' => 'x'], ReportStatusService::STATE_DONE, true],
        ];
    }

    public static function visitsCareProvider(): array
    {
        $empty = self::createConfiguredStub(VisitsCare::class, [
            'getDoYouLiveWithClient' => null,
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $incomplete = self::createConfiguredStub(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $done = self::createConfiguredStub(VisitsCare::class, [
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
        $empty = self::createConfiguredStub(Action::class, [
            'getDoYouExpectFinancialDecisions' => null,
            'getDoYouHaveConcerns' => null,
        ]);

        $incomplete = self::createConfiguredStub(Action::class, [
            'getDoYouExpectFinancialDecisions' => 'yes',
            'getDoYouHaveConcerns' => null,
        ]);

        $done = self::createConfiguredStub(Action::class, [
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
        $document = self::createStub(Document::class);

        return [
            [['getWishToProvideDocumentation' => 'no'], ReportStatusService::STATE_DONE],
            [['getDocuments' => new ArrayCollection([])], ReportStatusService::STATE_NOT_STARTED],
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
        $accounts = new ArrayCollection([self::createStub(BankAccount::class)]);
        $emptyAccounts = new ArrayCollection([]);

        return [
            [['getBankAccounts' => $emptyAccounts, 'getBankAccountsIncomplete' => $emptyAccounts], ReportStatusService::STATE_NOT_STARTED],
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
        $asset = self::createStub(Asset::class);

        return [
            [['getAssets' => new ArrayCollection([]), 'getNoAssetToAdd' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getAssets' => new ArrayCollection([$asset]), 'getNoAssetToAdd' => null], ReportStatusService::STATE_DONE],
            [['getAssets' => new ArrayCollection([$asset]), 'getNoAssetToAdd' => true], ReportStatusService::STATE_DONE],
            [['getAssets' => new ArrayCollection([]), 'getNoAssetToAdd' => true], ReportStatusService::STATE_DONE],
        ];
    }

    public static function debtsProvider(): array
    {
        $debt = self::createStub(Debt::class);

        return [
            [['getHasDebts' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => new ArrayCollection([$debt])], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => new ArrayCollection([$debt]), 'getDebtManagement' => ''], ReportStatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => new ArrayCollection([$debt]), 'getDebtManagement' => 'Payment plan'], ReportStatusService::STATE_DONE],
            [['getHasDebts' => 'no'], ReportStatusService::STATE_DONE],
        ];
    }

    public static function moneyTransferProvider(): array
    {
        $account1 = self::createStub(BankAccount::class);
        $account2 = self::createStub(BankAccount::class);
        $mt1 = self::createStub(MoneyTransfer::class);

        $bothAccounts = new ArrayCollection([$account1, $account2]);
        $noAccounts = new ArrayCollection([]);

        return [
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => new ArrayCollection([]), 'getNoTransfersToAdd' => null], ReportStatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => new ArrayCollection([$mt1]), 'getNoTransfersToAdd' => null], ReportStatusService::STATE_DONE],
            [['getBankAccounts' => $bothAccounts, 'getMoneyTransfers' => new ArrayCollection([]), 'getNoTransfersToAdd' => true], ReportStatusService::STATE_DONE],
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
        $report = self::createMock(Report::class);

        $report->method('getAvailableSections')->willReturn([Report::SECTION_GIFTS]);
        $report->method('getSectionStatusesCached')->willReturn([]);
        $report->method('giftsSectionCompleted')->willReturn(true);
        $report->method('getGifts')->willReturn(new ArrayCollection(['a gift']));

        $report->expects(self::once())->method('isDue')->willReturn(true);

        $sut = new ReportStatusService($report);
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }

    public function testGetStatusNotFinished(): void
    {
        $report = self::createMock(Report::class);

        $report->expects(self::once())->method('getAvailableSections')->willReturn([Report::SECTION_GIFTS]);
        $report->expects(self::once())->method('getSectionStatusesCached')->willReturn([]);
        $report->expects(self::once())->method('giftsSectionCompleted')->willReturn(true);
        $report->expects(self::once())->method('getGifts')->willReturn(new ArrayCollection(['a gift']));

        $report->expects(self::once())->method('isDue')->willReturn(false);

        $sut = new ReportStatusService($report);
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_FINISHED, $status);
    }

    public function testGetStatusNotStarted(): void
    {
        $report = self::createMock(Report::class);

        $report->expects(self::once())->method('getAvailableSections')->willReturn([Report::SECTION_GIFTS]);
        $report->expects(self::once())->method('getSectionStatusesCached')->willReturn([]);
        $report->expects(self::once())->method('giftsSectionCompleted')->willReturn(false);

        $sut = new ReportStatusService($report);
        $status = $sut->getStatus();
        self::assertEquals(Report::STATUS_NOT_STARTED, $status);
    }

    public function testGetStatusIgnoringDueDateReadyToSubmit(): void
    {
        $report = self::createMock(Report::class);

        $report->method('getAvailableSections')->willReturn([Report::SECTION_GIFTS]);
        $report->method('getSectionStatusesCached')->willReturn([]);
        $report->method('giftsSectionCompleted')->willReturn(true);
        $report->method('getGifts')->willReturn(new ArrayCollection(['a gift']));

        $sut = new ReportStatusService($report);
        $status = $sut->getStatusIgnoringDueDate();
        self::assertEquals(Report::STATUS_READY_TO_SUBMIT, $status);
    }
}

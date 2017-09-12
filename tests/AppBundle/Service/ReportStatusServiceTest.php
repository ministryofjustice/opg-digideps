<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\Debt;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\MoneyShortCategory;
use AppBundle\Entity\Report\MoneyTransactionShort;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\VisitsCare;
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
                'getBankAccounts'                   => [],
                'getBankAccountsIncomplete'         => [],
                'getExpenses'                       => [],
                'getPaidForAnything'                => null,
                'getGifts'                          => [],
                'getGiftsExist'                     => null,
                'getMoneyTransfers'                 => [],
                'getNoTransfersToAdd'               => null,
                'getAssets'                         => [],
                'getDecisions'                      => [],
                'getHasCapacityChanged'             => null,
                'getNoAssetToAdd'                   => null,
                'getContacts'                       => [],
                'getReasonForNoContacts'            => null,
                'getReasonForNoDecisions'           => null,
                'getVisitsCare'                     => m::mock(VisitsCare::class, [
                    'getDoYouLiveWithClient'       => null,
                    'getDoesClientReceivePaidCare' => null,
                    'getWhoIsDoingTheCaring'       => null,
                    'getDoesClientHaveACarePlan'   => null,
                ]),
                'getLifestyle'                     => m::mock(VisitsCare::class, [
                    'getCareAppointments'       => null,
                    'getDoesClientUndertakeSocialActivities' => null,
                ]),
                'getAction'                         => m::mock(Action::class, [
                    'getDoYouExpectFinancialDecisions' => null,
                    'getDoYouHaveConcerns'             => null,
                ]),
                'getActionMoreInfo'                 => null,
                'getMentalCapacity'                 => null,
                'hasMoneyIn'                        => false,
                'getMoneyTransactionsIn'            => [],
                'hasMoneyOut'                       => false,
                'getMoneyTransactionsOut'           => [],
                'getHasDebts'                       => null,
                'getDebts'                          => [],
                'getDebtsWithValidAmount'           => [],
                'getTotalsMatch'                    => null,
                'getBalanceMismatchExplanation'     => null,
                'getDocuments'                      => [],
                'getWishToProvideDocumentation'     => null,
                // 103
                'getMoneyShortCategoriesIn'         => [],
                'getMoneyShortCategoriesInPresent'  => [],
                'getMoneyTransactionsShortInExist'  => null,
                'getMoneyTransactionsShortIn'       => [],
                'getMoneyShortCategoriesOut'        => [],
                'getMoneyShortCategoriesOutPresent' => [],
                'getMoneyTransactionsShortOutExist' => null,
                'getMoneyTransactionsShortOut'      => [],
                'getType'                           => Report::TYPE_102,
                // 106
                'has106Flag'                        => false,
                'getFeesWithValidAmount'                           => [],
                'getReasonForNoFees'                => null,
                //'getExpenses'                       => [],
                //'getPaidForAnything'                => null,
            ]);

        return new StatusService($report);
    }

    public function decisionsProvider()
    {
        $decision = m::mock(\AppBundle\Entity\Decision::class);
        $mcEmpty = m::mock(\AppBundle\Entity\MentalCapacity::class, [
            'getHasCapacityChanged'   => null,
            'getMentalAssessmentDate' => null,
        ]);
        $mcPartial = m::mock(\AppBundle\Entity\MentalCapacity::class, [
            'getHasCapacityChanged'   => 'no',
            'getMentalAssessmentDate' => null,
        ]);
        $mcComplete = m::mock(\AppBundle\Entity\MentalCapacity::class, [
            'getHasCapacityChanged'   => 'no',
            'getMentalAssessmentDate' => new \DateTime('2016-01-01'),
        ]);

        return [
            [[], StatusService::STATE_NOT_STARTED, false],
            // incomplete
            [['getDecisions' => [$decision]], StatusService::STATE_INCOMPLETE, false],
            [['getReasonForNoDecisions' => 'x'], StatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcComplete], StatusService::STATE_INCOMPLETE, false],
            [['getMentalCapacity' => $mcPartial, 'getDecisions' => [$decision]], StatusService::STATE_INCOMPLETE, false],
            // done
            [['getMentalCapacity' => $mcComplete, 'getDecisions' => [$decision]], StatusService::STATE_DONE, true],
            [['getMentalCapacity' => $mcComplete, 'getReasonForNoDecisions' => 'x'], StatusService::STATE_DONE, true],
        ];
    }

    /**
     * @test
     * @dataProvider decisionsProvider
     */
    public function decisions($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getDecisionsState()['state']);
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
        $this->assertEquals($state, $object->getContactsState()['state']);
    }

    public function visitsCareProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient'       => null,
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring'       => null,
            'getDoesClientHaveACarePlan'   => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient'       => 'yes',
            'getDoesClientReceivePaidCare' => null,
            'getWhoIsDoingTheCaring'       => null,
            'getDoesClientHaveACarePlan'   => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient'       => 'yes',
            'getDoesClientReceivePaidCare' => 'yes',
            'getWhoIsDoingTheCaring'       => 'xxx',
            'getDoesClientHaveACarePlan'   => 'yes',
        ]);

        return [
            [['getVisitsCare' => $empty], StatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getVisitsCare' => $done], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider visitsCareProvider
     */
    public function visitsCare($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getVisitsCareState()['state']);
    }


    public function lifestyleProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getCareAppointments'       => null,
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getCareAppointments'       => 'yes',
            'getDoesClientUndertakeSocialActivities' => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getCareAppointments'       => 'yes',
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
     * @dataProvider lifestyleProvider
     */
    public function lifestyle($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getLifestyleState()['state']);
    }

    public function bankAccountProvider()
    {
        $account = m::mock(\AppBundle\Entity\Report\Account::class);

        return [
            [['getBankAccounts' => [], 'getBankAccountsIncomplete' => []], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$account], 'getBankAccountsIncomplete' => [$account]], StatusService::STATE_INCOMPLETE],
            [['getBankAccounts' => [$account], 'getBankAccountsIncomplete' => []], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider bankAccountProvider
     */
    public function bankAccount($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getBankAccountsState()['state']);
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
            [['getBankAccounts' => []], StatusService::STATE_DONE],
            [['getBankAccounts' => [$account1]], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyTransferProvider
     */
    public function moneyTransfer($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyTransferState()['state']);
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
        $this->assertEquals($state, $object->getMoneyInState()['state']);
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
        $this->assertEquals($state, $object->getMoneyOutState()['state']);
    }

    public function moneyInShortProvider()
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        return [
            [['getMoneyTransactionsShortInExist' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyTransactionsShortInExist' => null, 'getMoneyShortCategoriesInPresent' => [$cat]], StatusService::STATE_INCOMPLETE],
            [['getMoneyTransactionsShortInExist' => 'yes'], StatusService::STATE_NOT_STARTED],
            [['getMoneyTransactionsShortInExist' => 'yes', 'getMoneyTransactionsShortIn'=>[$t]], StatusService::STATE_DONE],
            [['getMoneyTransactionsShortInExist' => 'no'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyInShortProvider
     */
    public function moneyInShort($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyInShortState()['state']);
    }

    public function moneyOutShortProvider()
    {
        $cat = m::mock(MoneyShortCategory::class);
        $t = m::mock(MoneyTransactionShort::class);

        return [
            [['getMoneyTransactionsShortOutExist' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyTransactionsShortOutExist' => null, 'getMoneyShortCategoriesOutPresent' => [$cat]], StatusService::STATE_INCOMPLETE],
            [['getMoneyTransactionsShortOutExist' => 'yes', 'getMoneyTransactionsShortOut'=>[$t]], StatusService::STATE_DONE],
            [['getMoneyTransactionsShortOutExist' => 'no'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider moneyOutShortProvider
     */
    public function moneyOutShort($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getMoneyOutShortState()['state']);
    }

    public function expensesProvider()
    {
        $expense = m::mock(Expense::class);

        return [
            [['getExpenses' => []], StatusService::STATE_NOT_STARTED],
            [['getPaidForAnything' => 'yes'], StatusService::STATE_NOT_STARTED], //should never happen
            [['getPaidForAnything' => 'no'], StatusService::STATE_DONE],
            [['getExpenses' => [$expense], 'getPaidForAnything' => 'yes'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider expensesProvider
     */
    public function expenses($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getExpensesState()['state']);
    }

    public function paFeesExpensesProvider()
    {
        $fee = m::mock(Fee::class);
        $expense = m::mock(Expense::class);

        $feeDone1 = ['getFeesWithValidAmount'=>[$fee]];
        $feeDone2 = ['getReasonForNoFees'=>'x'];
        $expenseDone1 = ['getExpenses'=>[$expense], 'getPaidForAnything'=>'yes'];
        $expenseDone2 = ['getPaidForAnything'=>'no'];

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [$feeDone1, StatusService::STATE_INCOMPLETE],
            [$feeDone2, StatusService::STATE_INCOMPLETE],
            [$expenseDone1, StatusService::STATE_INCOMPLETE],
            [$expenseDone2, StatusService::STATE_INCOMPLETE],
            [$feeDone1 + $expenseDone1, StatusService::STATE_DONE],// no need to test all the combinations
            [$feeDone2 + $expenseDone2, StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider paFeesExpensesProvider
     */
    public function paFeeExpenses($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked(['has106Flag'=>true] + $mocks);
        $this->assertEquals($state, $object->getPaFeesExpensesState()['state']);
    }

    public function giftsProvider()
    {
        $expense = m::mock(Gift::class);

        return [
            [['getGifts' => []], StatusService::STATE_NOT_STARTED],
            [['getGiftsExist' => 'yes'], StatusService::STATE_NOT_STARTED], //should never happen
            [['getGiftsExist' => 'no'], StatusService::STATE_DONE],
            [['getGifts' => [$expense], 'getGiftsExist' => 'yes'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider giftsProvider
     */
    public function gifts($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getGiftsState()['state']);
    }

    public function documentsProvider()
    {
        $document = m::mock(Document::class);

        return [
            [['getWishToProvideDocumentation' => 'no'], StatusService::STATE_DONE],
            [['getDocuments' => []], StatusService::STATE_NOT_STARTED],
            [['getWishToProvideDocumentation' => 'yes', 'getDocuments' => []], StatusService::STATE_NOT_STARTED],
            [['getWishToProvideDocumentation' => 'yes', 'getDocuments' => [$document]], StatusService::STATE_DONE],
        ];
    }

    /**
     * @dataProvider documentsProvider
     */
    public function testGetDocumentState($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getDocumentsState()['state']);
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
        $this->assertEquals($state, $object->getAssetsState()['state']);
    }

    public function debtsProvider()
    {
        $debt = m::mock(Debt::class);

        return [
            [['getHasDebts' => false], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount'=>[$debt]], StatusService::STATE_DONE],
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
        $this->assertEquals($state, $object->getDebtsState()['state']);
    }

    public function actionsProvider()
    {
        $empty = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => null,
            'getDoYouHaveConcerns'             => null,
        ]);

        $incomplete = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => 'yes',
            'getDoYouHaveConcerns'             => null,
        ]);

        $done = m::mock(Action::class, [
            'getDoYouExpectFinancialDecisions' => 'yes',
            'getDoYouHaveConcerns'             => 'no',
        ]);

        return [
            [['getAction' => $empty], StatusService::STATE_NOT_STARTED],
            [['getAction' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getAction' => $done], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider actionsProvider
     */
    public function actions($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getActionsState()['state']);
    }

    public function otherInfoProvider()
    {
        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getActionMoreInfo' => 'mr'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider otherInfoProvider
     */
    public function otherinfo($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getOtherInfoState()['state']);
    }

    public function balanceMatchesProvider()
    {
        return [
            [['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => null], false],
            [['getTotalsMatch' => true, 'getBalanceMismatchExplanation' => 'something'], true],
            [['getTotalsMatch' => false, 'getBalanceMismatchExplanation' => 'something'], true],
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

    public function mockedMethodsCompletingReport($type, $has106Flag = false)
    {
        $ret = ['getType' => $type];

        $ret += array_pop($this->decisionsProvider())[0];
        $ret += array_pop($this->contactsProvider())[0];
        $ret += array_pop($this->visitsCareProvider())[0];
        $ret += array_pop($this->actionsProvider())[0];
        $ret += array_pop($this->otherInfoProvider())[0];
        $ret += array_pop($this->giftsProvider())[0];
        $ret += array_pop($this->documentsProvider())[0];

        if ($type == Report::TYPE_102) {
            $ret += array_pop($this->bankAccountProvider())[0];
            $ret += array_pop($this->expensesProvider())[0];
            $ret += array_pop($this->assetsProvider())[0];
            $ret += array_pop($this->debtsProvider())[0];
            $ret += array_pop($this->moneyTransferProvider())[0];
            $ret += array_pop($this->MoneyInProvider())[0];
            $ret += array_pop($this->MoneyOutProvider())[0];
        }

        if ($type == Report::TYPE_103) {
            $ret += array_pop($this->bankAccountProvider())[0];
            $ret += array_pop($this->expensesProvider())[0];
            $ret += array_pop($this->assetsProvider())[0];
            $ret += array_pop($this->debtsProvider())[0];
            $ret += array_pop($this->MoneyInShortProvider())[0];
            $ret += array_pop($this->MoneyOutShortProvider())[0];
        }

        if ($type == Report::TYPE_104) {
            $ret += array_pop($this->lifestyleProvider())[0];
        }

        if ($has106Flag && in_array($type, [Report::TYPE_102, Report::TYPE_103])) {
            $ret += array_pop($this->paFeesExpensesProvider())[0];
        }

        return $ret;
    }

    public function testGetRemainingSections()
    {
        // all empty
        $object = $this->getStatusServiceWithReportMocked([]);
        $this->assertNotEquals([], $object->getRemainingSections());

        // all complete 102
        $object = $this->getStatusServiceWithReportMocked($this->mockedMethodsCompletingReport(Report::TYPE_102));
        $this->assertEquals([], $object->getRemainingSections());

        // all complete 103
        $object = $this->getStatusServiceWithReportMocked($this->mockedMethodsCompletingReport(Report::TYPE_103));
        $this->assertEquals([], $object->getRemainingSections());

        // all complete 104
        $object = $this->getStatusServiceWithReportMocked($this->mockedMethodsCompletingReport(Report::TYPE_104));
        $this->assertEquals([], $object->getRemainingSections());

        // all complete 106
        $object = $this->getStatusServiceWithReportMocked($this->mockedMethodsCompletingReport(Report::TYPE_102, true));
        $this->assertEquals([], $object->getRemainingSections());
    }

    public function isReadyToSubmitBalanceProvider()
    {
        return [
            [['getRemainingSections' => ['s1'], 'balanceMatches' => false], false],
            [['getRemainingSections' => [], 'balanceMatches' => false], false],
            [['getRemainingSections' => [], 'balanceMatches' => true], true],
        ];
    }

    /**
     * @test
     * @dataProvider isReadyToSubmitBalanceProvider
     */
    public function isReadyToSubmitBalance($data, $expected)
    {
        $report = m::mock(Report::class);
        $object = m::mock(ReportStatusService::class . '[getRemainingSections,balanceMatches]', [$report]);

        foreach ($data as $method => $return) {
            $object->shouldReceive($method)->andReturn($return);
        }

        $this->assertEquals($expected, $object->isReadyToSubmit());
    }


    public static function getSectionStatusProvider()
    {
        return [
            [Report::TYPE_102, ['bankAccounts', 'moneyIn'], ['moneyInShort', 'lifestyle']],
            [Report::TYPE_103, ['bankAccounts', 'moneyInShort'], ['moneyIn', 'lifestyle', 'balance']],
            [Report::TYPE_104, ['lifestyle'], ['bankAccounts', 'moneyIn', 'moneyInShort', 'gifts', 'balance']],
        ];
    }

    /**
     * @dataProvider getSectionStatusProvider
     */
    public function testGetSectionStatus($type, array $expectedSections, array $unExpectedSections)
    {
        $report = $this->getStatusServiceWithReportMocked(['getType'=>$type]);
        foreach($expectedSections as $expectedSection) {
            $this->assertArrayHasKey($expectedSection, $report->getSectionStatus(), "$type should have $expectedSection section ");
        }
        foreach($unExpectedSections as $unExpectedSection) {
            $this->assertArrayNotHasKey($unExpectedSection, $report->getSectionStatus(), "$type should NOT have $unExpectedSection section ");
        }

    }
}

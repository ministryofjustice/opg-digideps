<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\MoneyShortCategory;
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
                'getBankAccounts'                   => [],
                'getBankAccountsIncomplete'         => [],
                'getExpenses'                       => [],
                'getPaidForAnything'                => null,
                'getGifts'                          => [],
                'getGiftsExist'                     => [],
                'getMoneyTransfers'                 => [],
                'getNoTransfersToAdd'               => null,
                'getAssets'                         => [],
                'getDecisions'                      => [],
                'getNoAssetToAdd'                   => null,
                'getContacts'                       => null,
                'getReasonForNoContacts'            => null,
                'getReasonForNoDecisions'           => null,
                'getVisitsCare'                     => null,
                'getAction'                         => null,
                'getActionMoreInfo'                 => null,
                'getMentalCapacity'                 => null,
                'hasMoneyIn'                        => false,
                'getMoneyTransactionsIn'            => [],
                'hasMoneyOut'                       => false,
                'getMoneyTransactionsOut'           => [],
                'getHasDebts'                       => null,
                'getDebts'                          => [],
                'getDebtsWithValidAmount'           => [],
                'isTotalsMatch'                     => null,
                'getBalanceMismatchExplanation'     => null,
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
        $this->assertEquals($state, $object->getVisitsCareState()['state']);
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

        return [
            [['getMoneyTransactionsShortInExist' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyTransactionsShortInExist' => null, 'getMoneyShortCategoriesInPresent' => [$cat]], StatusService::STATE_INCOMPLETE],
            [['getMoneyTransactionsShortInExist' => 'yes'], StatusService::STATE_DONE],
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

        return [
            [['getMoneyTransactionsShortOutExist' => null], StatusService::STATE_NOT_STARTED],
            [['getMoneyTransactionsShortOutExist' => null, 'getMoneyShortCategoriesOutPresent' => [$cat]], StatusService::STATE_INCOMPLETE],
            [['getMoneyTransactionsShortOutExist' => 'yes'], StatusService::STATE_DONE],
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
        $expense = m::mock(Expense::class, [
            'missingInfo' => false,
        ]);

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
        $this->assertEquals($state, $object->getDebtsState()['state']);
    }

    public function actionsProvider()
    {
        $actionIncomplete = m::mock(\AppBundle\Entity\Action::class, [
            'getDoYouHaveConcerns'             => true,
            'getDoYouExpectFinancialDecisions' => false,
        ]);

        $actionComplete = m::mock(\AppBundle\Entity\Action::class, [
            'getDoYouHaveConcerns'             => true,
            'getDoYouExpectFinancialDecisions' => true,
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

    public function mockedMethodsCompletingReport($type)
    {
        $ret = ['getType' => $type];

        $ret += array_pop($this->decisionsProvider())[0];
        $ret += array_pop($this->contactsProvider())[0];
        $ret += array_pop($this->visitsCareProvider())[0];
        $ret += array_pop($this->actionsProvider())[0];
        $ret += array_pop($this->otherInfoProvider())[0];
        $ret += array_pop($this->giftsProvider())[0];

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

        return $ret;
    }

    /**
     * @test
     */
    public function getRemainingSections()
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
}

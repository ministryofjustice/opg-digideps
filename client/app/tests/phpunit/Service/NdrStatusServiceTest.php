<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\Ndr\BankAccount;
use App\Entity\Ndr\ClientBenefitsCheck;
use App\Entity\Ndr\Debt;
use App\Entity\Ndr\Expense;
use App\Entity\Ndr\IncomeBenefit;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf;
use App\Entity\Ndr\Ndr;
use App\Entity\Ndr\VisitsCare;
use App\Service\NdrStatusService as StatusService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * //TODO consider using traits to re-use logic in ReportStatusServiceTest for common sections.
 */
class NdrStatusServiceTest extends TestCase
{
    /**
     * @test
     * @dataProvider visitsCareProvider
     */
    public function visitsCare($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getVisitsCareState()['state']);
    }

    /**
     * @return StatusService
     */
    private function getStatusServiceWithReportMocked(array $ndrMethods)
    {
        $ndr = m::mock(Ndr::class, $ndrMethods + [
                'getVisitsCare' => m::mock(VisitsCare::class, [
                    'getDoYouLiveWithClient' => null,
                    'getDoesClientHaveACarePlan' => null,
                    'getWhoIsDoingTheCaring' => null,
                    'getDoesClientHaveACarePlan' => null,
                    'getPlanMoveNewResidence' => null,
                ]),
                'getExpenses' => [],
                'getPaidForAnything' => null,
                'getStateBenefits' => [],
                'getStateBenefitsPresent' => [],
                'getReceiveStatePension' => null,
                'getReceiveOtherIncome' => null,
                'getExpectCompensationDamages' => null,
                'getOneOffPresent' => [],
                'getBankAccounts' => [],
                'getHasDebts' => null,
                'getDebtsWithValidAmount' => [],
                'getDebtManagement' => null,
                'getNoAssetToAdd' => null,
                'getAssets' => [],
                'incomeBenefitsStatus' => 'not-started',
                'getActionGiveGiftsToClient' => null,
                'getActionPropertyBuy' => null,
                'getActionPropertyMaintenance' => null,
                'getActionPropertySellingRent' => null,
                'getActionMoreInfo' => null,
                'getClientBenefitsCheck' => null,
            ]);

        return new StatusService($ndr);
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

    /**
     * @test
     * @dataProvider incomeBenefitsProvider
     */
    public function incomeBenefits($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getIncomeBenefitsState()['state']);
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

    /**
     * @test
     * @dataProvider banksProvider
     */
    public function banks($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getBankAccountsState()['state']);
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

    /**
     * @test
     * @dataProvider actionProvider
     */
    public function actions($mocks, $state)
    {
        $object = $this->getStatusServiceWithReportMocked($mocks);
        $this->assertEquals($state, $object->getActionsState()['state']);
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

    /**
     * @test
     */
    public function getRemainingSectionsAll()
    {
        $object = $this->getStatusServiceWithReportMocked([]);
        $rs = $object->getRemainingSections();
        $this->assertEquals('not-started', $rs['visitsCare']);
        $this->assertEquals('not-started', $rs['expenses']);
        $this->assertEquals('not-started', $rs['incomeBenefits']);
        $this->assertEquals('not-started', $rs['bankAccounts']);
        $this->assertEquals('not-started', $rs['assets']);
        $this->assertEquals('not-started', $rs['debts']);
        $this->assertEquals('not-started', $rs['actions']);
        $this->assertEquals('not-started', $rs['otherInfo']);
        $this->assertEquals('not-started', $rs['clientBenefitsCheck']);

        $this->assertEquals('notStarted', $object->getStatus());
    }

    public function getRemainingSectionsPartialProvider()
    {
        [
            $visitsCare,
            $expenses,
            $incomeBenefits,
            $banks,
            $assets,
            $debts,
            $action,
            $otherInfo,
            $clientBenefitsCheck
        ] = $this->buildSectionsByProviders();

        return [
            [array_pop($visitsCare)[0], 'visitsCare'],
            [array_pop($expenses)[0], 'expenses'],
            [array_pop($incomeBenefits)[0], 'incomeBenefits'],
            [array_pop($banks)[0], 'bankAccounts'],
            [array_pop($assets)[0], 'assets'],
            [array_pop($debts)[0], 'debts'],
            [array_pop($action)[0], 'actions'],
            [array_pop($otherInfo)[0], 'otherInfo'],
            [array_pop($clientBenefitsCheck)[0], 'clientBenefitsCheck'],
        ];
    }

    private function buildSectionsByProviders()
    {
        return [
            $this->visitsCareProvider(),
            $this->expensesProvider(),
            $this->incomeBenefitsProvider(),
            $this->banksProvider(),
            $this->assetsProvider(),
            $this->debtsProvider(),
            $this->actionProvider(),
            $this->otherInfoProvider(),
            $this->clientBenefitsCheckProvider(),
        ];
    }

    public function visitsCareProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => null,
            'getDoesClientHaveACarePlan' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
            'getPlanMoveNewResidence' => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientHaveACarePlan' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
            'getPlanMoveNewResidence' => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientHaveACarePlan' => 'yes',
            'getWhoIsDoingTheCaring' => 'xxx',
            'getDoesClientHaveACarePlan' => 'yes',
            'getPlanMoveNewResidence' => 'no',
        ]);

        return [
            // not started
            [['getVisitsCare' => $empty], StatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $incomplete], StatusService::STATE_INCOMPLETE],
            [['getVisitsCare' => $done], StatusService::STATE_DONE],
        ];
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

    public function incomeBenefitsProvider()
    {
        $ib = m::mock(IncomeBenefit::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [[
                'getExpectCompensationDamages' => true, //only this one complete
            ], StatusService::STATE_INCOMPLETE],
            [[
                'getReceiveStatePension' => true,
                'getReceiveOtherIncome' => false,
                'getExpectCompensationDamages' => false, ], StatusService::STATE_DONE],
        ];
    }

    public function banksProvider()
    {
        $bankAccount1 = m::mock(BankAccount::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => []], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$bankAccount1]], StatusService::STATE_DONE],
        ];
    }

    public function assetsProvider()
    {
        $asset = m::mock(Asset::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [], 'getNoAssetToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => false], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => null], StatusService::STATE_DONE],
        ];
    }

    public function debtsProvider()
    {
        $debt = m::mock(Debt::class);

        return [
            [['getHasDebts' => 'no'], StatusService::STATE_DONE],
            [['getHasDebts' => null], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => false], StatusService::STATE_NOT_STARTED],
            [['getHasDebts' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt]], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => ''], StatusService::STATE_INCOMPLETE],
            [['getHasDebts' => 'yes', 'getDebtsWithValidAmount' => [$debt], 'getDebtManagement' => 'Payment plan'], StatusService::STATE_DONE],
        ];
    }

    public function actionProvider()
    {
        $allDone = [
            'getActionGiveGiftsToClient' => 'yes',
            'getActionPropertyBuy' => 'yes',
            'getActionPropertyMaintenance' => 'yes',
            'getActionPropertySellingRent' => 'yes',
        ];

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getActionGiveGiftsToClient' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertyBuy' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertyMaintenance' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertySellingRent' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertyBuy' => 'yes', 'getActionPropertySellingRent' => 'yes'], StatusService::STATE_INCOMPLETE],
            [$allDone, StatusService::STATE_DONE],
        ];
    }

    public function otherInfoProvider()
    {
        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getActionMoreInfo' => 'mr'], StatusService::STATE_DONE],
        ];
    }

    public function clientBenefitsCheckProvider()
    {
        $clientBenefitsCheck = new ClientBenefitsCheck();
        $income = new ArrayCollection();
        $income->add(new MoneyReceivedOnClientsBehalf());

        $incomplete = [
            'getClientBenefitsCheck' => ($clientBenefitsCheck)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING),
        ];
        $doneDontKnowIncome = [
            'getClientBenefitsCheck' => ($clientBenefitsCheck)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING)
                ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_DONT_KNOW),
        ];
        $doneNoIncomeToAdd = [
            'getClientBenefitsCheck' => ($clientBenefitsCheck)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING)
                ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_NO),
        ];
        $incompleteMissingIncome = [
            'getClientBenefitsCheck' => ($clientBenefitsCheck)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING)
                ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_YES),
        ];
        $doneIncomeAdded = [
            'getClientBenefitsCheck' => ($clientBenefitsCheck)
                ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_IM_CURRENTLY_CHECKING)
                ->setDoOthersReceiveMoneyOnClientsBehalf(ClientBenefitsCheck::OTHER_MONEY_YES)
                ->setTypesOfMoneyReceivedOnClientsBehalf($income),
        ];

        return [
            'Nothing started' => [[], StatusService::STATE_NOT_STARTED],
            'Incomplete' => [$incomplete, StatusService::STATE_INCOMPLETE],
            'No income to add - dont know' => [$doneDontKnowIncome, StatusService::STATE_DONE],
            'No income to add - no income received' => [$doneNoIncomeToAdd, StatusService::STATE_DONE],
            'Income to add - no income added' => [$incompleteMissingIncome, StatusService::STATE_INCOMPLETE],
            'Income to add - income added' => [$doneIncomeAdded, StatusService::STATE_DONE],
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
        $this->assertFalse($object->isReadyToSubmit()); // enable when other sections are added
        $this->assertEquals('notFinished', $object->getStatus());
    }

    /**
     * @test
     */
    public function getReadyToSubmit()
    {
        $this->assertFalse($this->getStatusServiceWithReportMocked([])->isReadyToSubmit());

        [
            $visitsCare,
            $expenses,
            $incomeBenefits,
            $banks,
            $assets,
            $debts,
            $action,
            $otherInfo,
            $clientBenefitsCheck
        ] = $this->buildSectionsByProviders();

        $object = $this->getStatusServiceWithReportMocked(
            array_pop($visitsCare)[0]
            + array_pop($expenses)[0]
            + array_pop($incomeBenefits)[0]
            + array_pop($banks)[0]
            + array_pop($assets)[0]
            + array_pop($debts)[0]
            + array_pop($action)[0]
            + array_pop($otherInfo)[0]
            + array_pop($clientBenefitsCheck)[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
        $this->assertEquals('readyToSubmit', $object->getStatus());
        $this->assertEquals(NdrStatusService::STATE_DONE, $object->getSubmitState()['state']);
    }
}

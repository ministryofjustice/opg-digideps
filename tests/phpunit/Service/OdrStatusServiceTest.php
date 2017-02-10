<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\BankAccount;
use AppBundle\Entity\Odr\Expense;
use AppBundle\Entity\Odr\IncomeBenefit;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Odr\VisitsCare;
use AppBundle\Service\OdrStatusService as StatusService;
use Mockery as m;

/**
 * //TODO consider using traits to re-use logic in ReportStatusServiceTest for common sections
 */
class OdrStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $odrMethods
     *
     * @return StatusService
     */
    private function getStatusServiceWithReportMocked(array $odrMethods)
    {
        $odr = m::mock(Odr::class, $odrMethods + [
                'getVisitsCare' => m::mock(VisitsCare::class, [
                    'getDoYouLiveWithClient' => null,
                    'getDoesClientHaveACarePlan' => null,
                    'getWhoIsDoingTheCaring' => null,
                    'getDoesClientHaveACarePlan' => null,
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
                'getNoAssetToAdd' => null,
                'getAssets' => [],
                'incomeBenefitsStatus' => 'not-started',
                'getActionGiveGiftsToClient' => null,
                'getActionPropertyBuy' => null,
                'getActionPropertyMaintenance' => null,
                'getActionPropertySellingRent' => null,
                'getActionMoreInfo' => null,
            ]);

        return new StatusService($odr);
    }

    public function visitsCareProvider()
    {
        $empty = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => null,
            'getDoesClientHaveACarePlan' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $incomplete = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientHaveACarePlan' => null,
            'getWhoIsDoingTheCaring' => null,
            'getDoesClientHaveACarePlan' => null,
        ]);
        $done = m::mock(VisitsCare::class, [
            'getDoYouLiveWithClient' => 'yes',
            'getDoesClientHaveACarePlan' => 'yes',
            'getWhoIsDoingTheCaring' => 'xxx',
            'getDoesClientHaveACarePlan' => 'yes',
        ]);

        return [
            // not started
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

    public function incomeBenefitsProvider()
    {
        $ib = m::mock(IncomeBenefit::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [[
                'getExpectCompensationDamages'=>true, //only this one complete
                ], StatusService::STATE_INCOMPLETE],
            [[
                'getReceiveStatePension'=>true,
                'getReceiveOtherIncome'=>false,
                'getExpectCompensationDamages'=>false], StatusService::STATE_DONE],
        ];
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

    public function debtsProvider()
    {
        return [
            [['getHasDebts' => null], StatusService::STATE_NOT_STARTED],
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

    public function banksProvider()
    {
        $bankAccount1 = m::mock(BankAccount::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => []], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [$bankAccount1]], StatusService::STATE_DONE],
        ];
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

    public function assetsProvider()
    {
        $asset = m::mock(\AppBundle\Entity\Asset::class);

        return [
            [[], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [], 'getNoAssetToAdd' => null], StatusService::STATE_NOT_STARTED],
            [['getAssets' => [], 'getNoAssetToAdd' => true], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => false], StatusService::STATE_DONE],
            [['getAssets' => [$asset], 'getNoAssetToAdd' => null], StatusService::STATE_DONE],
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

    /**
     * @test
     * @dataProvider actionProvider
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

        $this->assertEquals('notStarted', $object->getStatus());
    }

    public function getRemainingSectionsPartialProvider()
    {
        return [
            [array_pop($this->visitsCareProvider())[0], 'visitsCare'],
            [array_pop($this->expensesProvider())[0], 'expenses'],
            [array_pop($this->incomeBenefitsProvider())[0], 'incomeBenefits'],
            [array_pop($this->banksProvider())[0], 'bankAccounts'],
            [array_pop($this->assetsProvider())[0], 'assets'],
            [array_pop($this->debtsProvider())[0], 'debts'],
            [array_pop($this->actionProvider())[0], 'actions'],
            [array_pop($this->otherInfoProvider())[0], 'otherInfo'],

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
        $this->assertFalse($object->isReadyToSubmit());// enable when other sections are added
        $this->assertEquals('notFinished', $object->getStatus());
    }

    /**
     * @test
     */
    public function getReadyToSubmit()
    {
        $this->assertFalse($this->getStatusServiceWithReportMocked([])->isReadyToSubmit());

        $object = $this->getStatusServiceWithReportMocked(
            array_pop($this->visitsCareProvider())[0]
            + array_pop($this->expensesProvider())[0]
            + array_pop($this->incomeBenefitsProvider())[0]
            + array_pop($this->banksProvider())[0]
            + array_pop($this->assetsProvider())[0]
            + array_pop($this->debtsProvider())[0]
            + array_pop($this->actionProvider())[0]
            + array_pop($this->otherInfoProvider())[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
        $this->assertEquals('readyToSubmit', $object->getStatus());
        $this->assertEquals(OdrStatusService::STATE_DONE, $object->getSubmitState()['state']);
    }
}

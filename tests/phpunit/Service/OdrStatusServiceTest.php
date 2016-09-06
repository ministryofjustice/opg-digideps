<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\BankAccount;
use AppBundle\Entity\Odr\Debt;
use AppBundle\Entity\Odr\VisitsCare;
use Mockery as m;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Service\OdrStatusService as StatusService;

class OdrStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $odrMethods
     *
     * @return StatusService
     */
    private function getOdrMocked(array $odrMethods)
    {
        $odr = m::mock(Odr::class, $odrMethods + [
                'getVisitsCare' => [],
                'getBankAccounts' => [],
                'getHasDebts' => null,
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
        $visitsCareOk = m::mock(VisitsCare::class, [
            'missingInfo' => false,
        ]);

        $visitsCareErr = m::mock(VisitsCare::class, [
            'missingInfo' => true,
        ]);

        return [
            // not started
            [[], StatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $visitsCareErr], StatusService::STATE_NOT_STARTED],
            // done
            [['getVisitsCare' => $visitsCareOk], StatusService::STATE_DONE],
        ];
    }

    /**
     * @dataProvider visitsCareProvider
     */
    public function testVisitsCare($mocks, $state)
    {
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getVisitsCareState());
    }


    public function financeProvider()
    {
        $bankAccount1 = m::mock(BankAccount::class);

        return [
            // not started
            [[], StatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => [], 'incomeBenefitsStatus'=>'not-started'], StatusService::STATE_NOT_STARTED],
            // incomplete
            [['getBankAccounts' => [$bankAccount1], 'incomeBenefitsStatus'=>'not-started'], StatusService::STATE_INCOMPLETE],
            [['getBankAccounts' => [], 'incomeBenefitsStatus'=>'incomplete'], StatusService::STATE_INCOMPLETE],
            [['getBankAccounts' => [], 'incomeBenefitsStatus'=>'done'], StatusService::STATE_INCOMPLETE],
            // done
            [['getBankAccounts' => [$bankAccount1], 'incomeBenefitsStatus'=>'done'], StatusService::STATE_DONE],
        ];
    }

    /**
     * @dataProvider financeProvider
     */
    public function testFinance($mocks, $state)
    {
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getFinanceState());
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
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getAssetsDebtsState());
    }

    public function actionProvider()
    {
        $allDone = [
            'getActionGiveGiftsToClient' => 'yes',
            'getActionPropertyBuy' => 'yes',
            'getActionPropertyMaintenance' => 'yes',
            'getActionPropertySellingRent' => 'yes',
            'getActionMoreInfo' => 'yes',
        ];

        return [
            [[], StatusService::STATE_NOT_STARTED],
            // missing sth
            [['getActionGiveGiftsToClient' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertyBuy' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertyMaintenance' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionPropertySellingRent' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionMoreInfo' => 'yes'], StatusService::STATE_INCOMPLETE],
            [['getActionMoreInfo' => 'yes', 'getActionPropertySellingRent' => 'yes'], StatusService::STATE_INCOMPLETE],
            // done
            [$allDone, StatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider actionProvider
     */
    public function actions($mocks, $state)
    {
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getActionsState());
    }

    /**
     * @test
     */
    public function getRemainingSectionsAll()
    {
        $object = $this->getOdrMocked([]);
        $rs = $object->getRemainingSections();
        $this->assertCount(4, $rs);
        $this->assertEquals('not-started', $rs['visitsCare']);
        $this->assertEquals('not-started', $rs['finance']);
        $this->assertEquals('not-started', $rs['assetsDebts']);
        $this->assertEquals('not-started', $rs['actions']);
    }

    public function getRemainingSectionsPartialProvider()
    {
        return [
            // create using last DONE section of each provider
            [array_pop($this->visitsCareProvider())[0], 'visitsCare'],
            [array_pop($this->financeProvider())[0], 'finance'],
            [array_pop($this->assetsDebtsProvider())[0], 'assets'],
            [array_pop($this->actionProvider())[0], 'actions'],
        ];
    }

    /**
     * @test
     * @dataProvider getRemainingSectionsPartialProvider
     */
    public function getRemainingSectionsPartial($provider, $keyRemoved)
    {
        $object = $this->getOdrMocked($provider);
        $this->assertArrayNotHasKey($keyRemoved, $object->getRemainingSections());
        $this->assertFalse($object->isReadyToSubmit());// enable when other sections are added
    }

    /**
     * @test
     */
    public function getReadyToSubmit()
    {
        $this->assertFalse($this->getOdrMocked([])->isReadyToSubmit());

        $object = $this->getOdrMocked(
            array_pop($this->visitsCareProvider())[0]
            + array_pop($this->financeProvider())[0]
            + array_pop($this->assetsDebtsProvider())[0]
            + array_pop($this->actionProvider())[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
    }
}

<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\BankAccount;
use AppBundle\Entity\Odr\VisitsCare;
use Mockery as m;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Service\OdrStatusService;

class OdrStatusServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $odrMethods
     *
     * @return OdrStatusService
     */
    private function getOdrMocked(array $odrMethods)
    {
        $odr = m::mock(Odr::class, $odrMethods + [
                'getVisitsCare' => [],
                'getBankAccounts' => [],
            ]);

        return new OdrStatusService($odr);
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
            [[], OdrStatusService::STATE_NOT_STARTED],
            [['getVisitsCare' => $visitsCareErr], OdrStatusService::STATE_NOT_STARTED],
            // done
            [['getVisitsCare' => $visitsCareOk], OdrStatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider visitsCareProvider
     */
    public function visitsCare($mocks, $state)
    {
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getVisitsCareState());
    }


    public function financeProvider()
    {
        $bankAccount1 = m::mock(BankAccount::class);

        return [
            // not started
            [[], OdrStatusService::STATE_NOT_STARTED],
            [['getBankAccounts' => []], OdrStatusService::STATE_NOT_STARTED],
            // done
            [['getBankAccounts' => [$bankAccount1]], OdrStatusService::STATE_DONE],
        ];
    }

    /**
     * @test
     * @dataProvider financeProvider
     */
    public function financeCare($mocks, $state)
    {
        $object = $this->getOdrMocked($mocks);
        $this->assertEquals($state, $object->getFinanceState());
    }


    public function getRemainingSectionsPartialProvider()
    {
        return [
            // create using last DONE section of each provider
            [array_pop($this->visitsCareProvider())[0], 'visitsCare'],
            [array_pop($this->financeProvider())[0], 'finance'],
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
        //$this->assertFalse($object->isReadyToSubmit());// enable when other sections are added
    }

    /**
     * @test
     */
    public function getRemainingSectionsNone()
    {
        $object = $this->getOdrMocked(
            array_pop($this->visitsCareProvider())[0]
            + array_pop($this->financeProvider())[0]
        );

        $this->assertEquals([], $object->getRemainingSections());
        $this->assertTrue($object->isReadyToSubmit());
    }
}

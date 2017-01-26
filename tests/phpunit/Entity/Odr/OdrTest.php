<?php

namespace AppBundle\Entity\Odr;

use Mockery as m;

class OdrTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Odr $report */
    private $odr;

    protected function setUp()
    {
        $this->odr = new Odr;
        $this->incomeTicked = new IncomeBenefit('t1', true);
        $this->incomeUnticked = new IncomeBenefit('t2', false);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testCountRecordsPresent()
    {
        $this->assertCount(0, $this->odr->recordsPresent(null));
        $this->assertCount(0, $this->odr->recordsPresent([]));
        $this->assertCount(0, $this->odr->recordsPresent([$this->incomeUnticked, $this->incomeUnticked]));
        $this->assertCount(1, $this->odr->recordsPresent([$this->incomeTicked, $this->incomeUnticked]));
        $this->assertCount(1, $this->odr->recordsPresent([$this->incomeTicked]));
    }

    public function testgetStateBenefitOther()
    {
        $odr = new Odr;

        $odr->setStateBenefits([]);
        $this->assertNull($odr->getStateBenefitOther());

        $odr->setStateBenefits([new IncomeBenefit('other_benefits', true)]);
        $this->assertEquals('other_benefits', $odr->getStateBenefitOther()->getTypeId());
    }

    public function testIncomeBenefitsStatus()
    {
        $this->assertEquals('not-started', $this->odr->incomeBenefitsStatus());

        $this->odr->setStateBenefits([$this->incomeTicked]);
        $this->assertEquals('incomplete', $this->odr->incomeBenefitsStatus(), 'state benefits and one-off should be ignored');

        $this->odr->setReceiveStatePension('yes');
        $this->odr->setReceiveOtherIncome('yes');
        $this->odr->setReceiveOtherIncomeDetails('..');
        $this->odr->setExpectCompensationDamages('yes');
        $this->odr->setExpectCompensationDamagesDetails('..');

        // state benefits and one-off should be ignored
        $this->odr->setStateBenefits([$this->incomeUnticked]);
        $this->odr->setOneOff([$this->incomeUnticked]);

        $this->assertEquals('done', $this->odr->incomeBenefitsStatus());
    }

    public function testGetAssetsTotalValue()
    {
        $this->odr->setAssets([
            m::mock(AssetOther::class, ['getValueTotal'=>1]),
            m::mock(AssetProperty::class, ['getValueTotal'=>2]),
        ]);

        $this->assertEquals(3, $this->odr->getAssetsTotalValue());
    }
}

<?php

namespace AppBundle\Entity\Odr;

use Mockery as m;

class OdrTest extends \PHPUnit_Framework_TestCase
{
    /** @var Odr $report */
    private $odr;

    protected function setUp()
    {
        $this->odr = new Odr();
        $this->incomeTicked = new StateBenefit('t1', true);
        $this->incomeUnticked = new StateBenefit('t2', false);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testgetStateBenefitOther()
    {
        $odr = new Odr();

        $odr->setStateBenefits([]);
        $this->assertNull($odr->getStateBenefitOther());

        $odr->setStateBenefits([new StateBenefit('other_benefits', true)]);
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

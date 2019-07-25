<?php

namespace AppBundle\Entity\Ndr;

use Mockery as m;

class NdrTest extends \PHPUnit_Framework_TestCase
{
    /** @var Ndr $report */
    private $ndr;

    protected function setUp()
    {
        $this->ndr = new Ndr();
        $this->incomeTicked = new StateBenefit('t1', true);
        $this->incomeUnticked = new StateBenefit('t2', false);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testgetStateBenefitOther()
    {
        $ndr = new Ndr();

        $ndr->setStateBenefits([]);
        $this->assertNull($ndr->getStateBenefitOther());

        $ndr->setStateBenefits([new StateBenefit('other_benefits', true)]);
        $this->assertEquals('other_benefits', $ndr->getStateBenefitOther()->getTypeId());
    }

    public function testIncomeBenefitsStatus()
    {
        $this->assertEquals('not-started', $this->ndr->incomeBenefitsStatus());

        $this->ndr->setStateBenefits([$this->incomeTicked]);
        $this->assertEquals('incomplete', $this->ndr->incomeBenefitsStatus(), 'state benefits and one-off should be ignored');

        $this->ndr->setReceiveStatePension('yes');
        $this->ndr->setReceiveOtherIncome('yes');
        $this->ndr->setReceiveOtherIncomeDetails('..');
        $this->ndr->setExpectCompensationDamages('yes');
        $this->ndr->setExpectCompensationDamagesDetails('..');

        // state benefits and one-off should be ignored
        $this->ndr->setStateBenefits([$this->incomeUnticked]);
        $this->ndr->setOneOff([$this->incomeUnticked]);

        $this->assertEquals('done', $this->ndr->incomeBenefitsStatus());
    }

    public function testGetAssetsTotalValue()
    {
        $this->ndr->setAssets([
            m::mock(AssetOther::class, ['getValueTotal'=>1]),
            m::mock(AssetProperty::class, ['getValueTotal'=>2]),
        ]);

        $this->assertEquals(3, $this->ndr->getAssetsTotalValue());
    }
}

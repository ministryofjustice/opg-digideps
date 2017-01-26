<?php

namespace AppBundle\Entity\Odr;

use Mockery as m;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AssetOther
     */
    private $assetOther;

    /**
     * @var AssetProperty
     */
    private $assetProp;

    protected function setUp()
    {
        $this->assetOther = new AssetOther();
        $this->assetProp = new AssetProperty();
    }

    public function tearDown()
    {
        m::close();
    }

    public function testOtherGetValueTotal()
    {
        $this->assetOther->setValue(1.1);
        $this->assertEquals(1.1, $this->assetOther->getValueTotal());
    }

    public function propertyGetValueTotalProvider()
    {
        return [
            [AssetProperty::OWNED_PARTLY, 100000, 0, 0],
            [AssetProperty::OWNED_PARTLY, 100000, 60, 60000],
            [AssetProperty::OWNED_PARTLY, null, null, null],

            [AssetProperty::OWNED_FULLY, 100000, 0, 100000],
            [AssetProperty::OWNED_FULLY, 100000, 60, 100000],
            [AssetProperty::OWNED_FULLY, null, null, null],
        ];
    }

    /**
     * @dataProvider propertyGetValueTotalProvider
     * @test
     */
    public function testPropertyGetValueTotal($owned, $value, $ownPercentage, $expected)
    {
        $this->assetProp->setValue($value)->setOwned($owned)->setOwnedPercentage($ownPercentage);
        $this->assertEquals($expected, $this->assetProp->getValueTotal());
    }
}

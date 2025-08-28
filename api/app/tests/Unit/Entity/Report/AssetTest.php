<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity\Report;

use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use PHPUnit\Framework\TestCase;

final class AssetTest extends TestCase
{
    private AssetOther $object;
    private AssetProperty $property;

    protected function setUp(): void
    {
        $this->object = new AssetOther();
        $this->property = new AssetProperty();
    }

    public function testSetterGetters(): void
    {
        //        $this->assertEquals('123456', $this->object->setExplanation('123456')->getExplanation());
        $this->assertEquals('123456', $this->object->setTitle('123456')->getTitle());
        $this->assertEquals('123456', $this->object->setValue('123456')->getValue());

        $this->assertEquals('123456', $this->property->setOccupants('123456')->getOccupants());
    }

    public function testgetValueTotal(): void
    {
        $this->object->setValue(1.2);
        $this->assertEquals(1.2, $this->object->getValueTotal());

        $this->property->setOwned(AssetProperty::OWNED_FULLY)->setValue(100);
        $this->assertEquals(100, $this->property->getValueTotal());

        $this->property->setOwned(AssetProperty::OWNED_PARTLY)->setOwnedPercentage(50)->setValue(1000);
        $this->assertEquals(500, $this->property->getValueTotal());
    }
}

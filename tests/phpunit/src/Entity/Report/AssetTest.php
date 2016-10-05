<?php

namespace AppBundle\Entity\Report;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Asset
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new AssetOther();
        $this->property = new AssetProperty();
    }

    public function testSetterGetters()
    {
        //        $this->assertEquals('123456', $this->object->setExplanation('123456')->getExplanation());
        $this->assertEquals('123456', $this->object->setTitle('123456')->getTitle());
        $this->assertEquals('123456', $this->object->setValue('123456')->getValue());

        $this->assertEquals('123456', $this->property->setOccupants('123456')->getOccupants());
    }
}

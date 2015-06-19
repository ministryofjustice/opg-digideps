<?php
namespace AppBundle\Entity;

use Mockery as m;

class AssetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Asset
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Asset();
    }

    public function testSetterGetters()
    {
//        $this->assertEquals('123456', $this->object->setExplanation('123456')->getExplanation());
        $this->assertEquals('123456', $this->object->setTitle('123456')->getTitle());
        $this->assertEquals('123456', $this->object->setValue('123456')->getValue());
    }
    
    public function testApplyUserFilter()
    {
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'getRootAliases' => [ 0 => 'q'],
                                           'andWhere' => $mockQueryBuilder,
                                           'setParameter' => $mockQueryBuilder, 
                                           'join' => $mockQueryBuilder ]);
        
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder',  Asset::applyUserFilter($mockQueryBuilder, 1));
    }

    
}

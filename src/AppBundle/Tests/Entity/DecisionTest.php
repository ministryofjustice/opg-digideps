<?php
namespace AppBundle\Entity;

use Mockery as m;

class DecisionTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyUserFilter()
    {
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'getRootAliases' => [ 0 => 'q'],
                                           'andWhere' => $mockQueryBuilder,
                                           'setParameter' => $mockQueryBuilder, 
                                           'join' => $mockQueryBuilder ]);
        
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', Decision::applyUserFilter($mockQueryBuilder, 1));
    }
}
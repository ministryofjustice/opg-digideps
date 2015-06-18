<?php
namespace AppBundle\Entity;

use Mockery as m;

class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyUserFilter()
    {
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'getRootAliases' => [ 0 => 'q'],
                                           'andWhere' => $mockQueryBuilder,
                                           'setParameter' => $mockQueryBuilder, 
                                           'join' => $mockQueryBuilder ]);
        
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', Contact::applyUserFilter($mockQueryBuilder, 1));
    }
}
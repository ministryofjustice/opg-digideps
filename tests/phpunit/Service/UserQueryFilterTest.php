<?php
namespace AppBundle\Tests\Service;

use Mockery as m;
use AppBundle\Service\UserQueryFilter;

class UserQueryFilterTest extends \PHPUnit_Framework_TestCase
{
    private $userQueryFilter;
    
    public function setUp()
    {
        $this->userQueryFilter = new UserQueryFilter();
    }
    
    public function testPassSetUser()
    {
        $mockUser = m::mock('AppBundle\Entity\User');
        $this->assertInstanceOf('AppBundle\Service\UserQueryFilter', $this->userQueryFilter->setUser($mockUser));
    }
    
    public function testFilterByUser()
    {
        //mock user
        $mockUser = m::mock('AppBundle\Entity\User');
        $mockUser->shouldReceive('getId')->andReturn(1);
        
        //mock query builder
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'join' => $mockQueryBuilder, 'andWhere' => $mockQueryBuilder, 'setParameter' => $mockQueryBuilder, 'getRootAliases' => [ 0 => 'q']]);
        
        $this->userQueryFilter->setUser($mockUser);
        
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $this->userQueryFilter->filterByUser($mockQueryBuilder, 'AppBundle\Entity\Account'));
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage AppBundle\Entity\User must implement UserFilterInterface to appy user filter
     */
    public function testFilterByUserThrowException()
    {
       //mock user
        $mockUser = m::mock('AppBundle\Entity\User');
        $mockUser->shouldReceive('getId')->andReturn(1);
        
        //mock query builder
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'join' => $mockQueryBuilder, 'andWhere' => $mockQueryBuilder, 'setParameter' => $mockQueryBuilder, 'getRootAliases' => [ 0 => 'q']]);
        
        $this->userQueryFilter->setUser($mockUser);
        
        $this->userQueryFilter->filterByUser($mockQueryBuilder, 'AppBundle\Entity\User'); 
    }
}
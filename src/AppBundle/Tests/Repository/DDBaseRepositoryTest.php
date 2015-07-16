<?php
namespace AppBundle\Tests\Repository;

use Mockery as m;

class UserQueryFilterTest extends \PHPUnit_Framework_TestCase
{
    private $ddBaseRepository;
    
    public function setUp()
    {
        $this->ddBaseRepository = m::mock("AppBundle\Repository\DDBaseRepository")->makePartial();
    }
    
    public function testSetQueryFilter()
    {
       $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
       $this->assertInstanceOf("AppBundle\Repository\DDBaseRepository", $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter)); 
    }
    
    public function testSetFilterByUser()
    {
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
        
        $mockUserQueryFilter->shouldReceive("filterByUser")->andReturn($mockQueryBuilder);
        
        $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter);
        
        $this->assertInstanceOf("Doctrine\ORM\QueryBuilder", $this->ddBaseRepository->filterByUser($mockQueryBuilder));
    }
    
    public function testFind()
    {
        $mockQuery = m::mock(new \stdClass());
        $mockQuery->shouldReceive([ 'getOneOrNullResult' => new \stdClass()]);
        
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'where' => $mockQueryBuilder, 'setParameter' => $mockQueryBuilder, 'getQuery' => $mockQuery ]);
        
        $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
        $mockUserQueryFilter->shouldReceive("filterByUser")->andReturn($mockQueryBuilder);
        
        $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter); 
        $this->ddBaseRepository->shouldReceive([ 'createQueryBuilder' => $mockQueryBuilder,
                                                 'filterByUser' => $mockQueryBuilder
                                               ]);
        
        $this->assertInstanceOf('\stdClass', $this->ddBaseRepository->find(1));
    }
    
    public function testFindAll()
    { 
        $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
        
        $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter); 
        $this->ddBaseRepository->shouldReceive([ 'findBy' => []]);
        
        $this->assertInternalType('array', $this->ddBaseRepository->findAll());
    }
    
    public function testFindOneBy()
    {
        $mockQuery = m::mock(new \stdClass());
        $mockQuery->shouldReceive([ 'getOneOrNullResult' => new \stdClass() ]);
        
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'andWhere' => $mockQueryBuilder, 
                                           'setParameter' => $mockQueryBuilder, 
                                           'getQuery' => $mockQuery,
                                           'addOrderBy' => $mockQueryBuilder ]);
        
        $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
        $mockUserQueryFilter->shouldReceive("filterByUser")->andReturn($mockQueryBuilder);
        
        $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter); 
        $this->ddBaseRepository->shouldReceive([ 'createQueryBuilder' => $mockQueryBuilder,
                                                 'filterByUser' => $mockQueryBuilder
                                               ]);
        
        $this->assertInstanceOf('\stdClass', $this->ddBaseRepository->findOneBy([ 'report_id' => 1 ]));
        $this->assertInstanceOf('\stdClass', $this->ddBaseRepository->findOneBy([ 'report_id' => 1 ], ['date' => 1 ]));
    }
    
    
    public function testFindBy()
    {
        $mockQuery = m::mock(new \stdClass());
        $mockQuery->shouldReceive([ 'execute' => [] ]);
        
        $mockQueryBuilder = m::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder->shouldReceive([ 'andWhere' => $mockQueryBuilder, 
                                           'setParameter' => $mockQueryBuilder, 
                                           'getQuery' => $mockQuery,
                                           'addOrderBy' => $mockQueryBuilder,
                                           'setMaxResults' => null,
                                           'setFirstResult' => null ]);
        
        $mockUserQueryFilter = m::mock("AppBundle\Service\UserQueryFilter");
        $mockUserQueryFilter->shouldReceive("filterByUser")->andReturn($mockQueryBuilder);
        
        $this->ddBaseRepository->setQueryFilter($mockUserQueryFilter); 
        $this->ddBaseRepository->shouldReceive([ 'createQueryBuilder' => $mockQueryBuilder,
                                                 'filterByUser' => $mockQueryBuilder
                                               ]);
        
        $this->assertInternalType('array', $this->ddBaseRepository->findBy([ 'report_id' => 1 ]));
        $this->assertInternalType('array', $this->ddBaseRepository->findBy([ 'report_id' => 1 ],['date' => 1 ]));
        $this->assertInternalType('array', $this->ddBaseRepository->findBy([ 'report_id' => 1 ],['date' => 1 ],1));
        $this->assertInternalType('array', $this->ddBaseRepository->findBy([ 'report_id' => 1 ],['date' => 1 ],20));
    }
}
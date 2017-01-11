<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\ReportService;
use AppBundle\Service\StatsService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Mockery as m;

class StatsServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $statsService;
    protected $queryMock;
    public function setUp()
    {
        $this->queryMock = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(['getResult'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilderMock->expects($this->any())
            ->method('leftJoin')
            ->will($this->returnValue($queryBuilderMock));
        $queryBuilderMock->expects($this->any())
            ->method('where')
            ->will($this->returnValue($queryBuilderMock));
        $queryBuilderMock->expects($this->any())
            ->method('orderBy')
            ->will($this->returnValue($queryBuilderMock));
        $queryBuilderMock->expects($this->any())
            ->method('setParameter')
            ->will($this->returnValue($queryBuilderMock));
        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($this->queryMock);

        $userRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userRepositoryMock->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        $reportServiceMock = $this->getMockBuilder(ReportService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->statsService = new StatsService($userRepositoryMock, $reportServiceMock);
    }

    public function testGetRecordsForEmpty()
    {
        $records = [];
        $this->queryMock->expects($this->once())
            ->method('getResult')
            ->willReturn($records);
        $records = $this->statsService->getRecords();
        $this->assertEmpty($records);
    }
    public function testGetRecordsWithData()
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->once())
            ->method('getClients')
            ->will($this->returnValue([]));

        $records = [$user];
        $this->queryMock->expects($this->once())
            ->method('getResult')
            ->willReturn($records);
        $returnRecords = $this->statsService->getRecords();

        $this->assertTrue(is_array($returnRecords));
    }
}

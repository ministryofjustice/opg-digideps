<?php

namespace App\Tests\Unit\Repository;

use App\Entity\ClientInterface;
use App\Entity\Report\Fee;
use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use App\Service\Search\ClientSearchFilter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReportRepositoryTest extends TestCase
{
    private ReportRepository $sut;
    private EntityRepository|MockInterface $mockReport;
    private MockInterface|EntityManager $mockEm;

    public function setUp(): void
    {
        $this->mockEm = m::mock(EntityManagerInterface::class);
        $mockManagerRegistry = m::mock(ManagerRegistry::class);
        $mockMetaClass = m::mock(ClassMetadata::class);
        $this->mockParameterBag = m::mock(ParameterBag::class);

        $mockManagerRegistry->shouldReceive('getManagerForClass')->andReturn($this->mockEm);
        $this->mockEm->shouldReceive('getClassMetadata')->andReturn($mockMetaClass);

        $clientSearchFilter = m::mock(ClientSearchFilter::class);
        $this->mockReport = m::mock(Report::class);
        $mockClient = m::mock(ClientInterface::class);

        $this->mockReport->shouldReceive('getClient')
            ->zeroOrMoreTimes()
            ->andReturn($mockClient);

        $this->sut = new ReportRepository($mockManagerRegistry, $clientSearchFilter);
    }

    /**
     * @throws ORMException
     */
    public function testAddFeesToReportIfMissingForNonPAUser()
    {
        $this->mockReport->shouldReceive('isPAReport')->andReturn(false);

        $this->assertNull($this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    /**
     * @throws ORMException
     */
    public function testAddFeesToReportIfMissingForPAUserWithFeesMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn([]);

        $this->mockReport->shouldReceive('addFee')->times(count(Fee::$feeTypeIds))->andReturnSelf();

        $this->mockEm->shouldReceive('persist')->times(count(Fee::$feeTypeIds));

        $this->mockReport->shouldReceive('isPAReport')->andReturn(true);

        $this->assertEquals(7, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    /**
     * @throws ORMException
     */
    public function testAddFeesToReportIfMissingForPAUserWithFeesNotMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn(['foo']);

        $this->mockReport->shouldReceive('addFee')->never();

        $this->mockEm->shouldReceive('persist')->never();

        $this->mockReport->shouldReceive('isPAReport')->andReturn(true);

        $this->assertEquals(0, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }
}

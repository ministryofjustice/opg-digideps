<?php

namespace Tests\AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use AppBundle\Entity\ClientInterface;
use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Fixtures;
use Mockery as m;

class ReportRepositoryTest extends WebTestCase
{
    /**
     * @var Fixtures|null
     */
    private static $fixtures;
    private static $repo;

    /**
     * @var ReportRepository
     */
    private $sut;

    /**
     * @var Report | MockInterface
     */
    private $mockReport;

    /**
     * @var EntityManagerInterface | MockInterface
     */
    private $mockEm;

    /** @var ClientInterface | MockInterface */
    private $mockClient;

    private $mockMetaClass;

    public function setUp(): void
    {
        $this->mockEm = m::mock(EntityManagerInterface::class);
        $this->mockMetaClass = m::mock(ClassMetadata::class);
        $this->mockReport = m::mock(Report::class);
        $this->mockClient = m::mock(ClientInterface::class);

        $this->mockReport->shouldReceive('getClient')
            ->zeroOrMoreTimes()
            ->andReturn($this->mockClient);

        $this->sut = new ReportRepository($this->mockEm, $this->mockMetaClass);
    }

    public function testAddFeesToReportIfMissingForNonPAUser()
    {

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('isPaDeputy')->andReturn(false);

        $this->mockClient->shouldReceive('getUsers')->andReturn(new ArrayCollection([$mockUser]));

        $this->assertNull($this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn([]);

        $this->mockReport->shouldReceive('addFee')->times(count(Fee::$feeTypeIds))->andReturnSelf();

        $this->mockEm->shouldReceive('persist')->times(count(Fee::$feeTypeIds));
        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('isPaDeputy')->andReturn(true);

        $this->mockClient->shouldReceive('getUsers')->andReturn(new ArrayCollection([$mockUser]));

        $this->assertEquals(7, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesNotMissing()
    {
        $this->mockReport->shouldReceive('getFees')->andReturn(['foo']);

        $this->mockReport->shouldReceive('addFee')->never();

        $this->mockEm->shouldReceive('persist')->never();

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('isPaDeputy')->andReturn(true);

        $this->mockClient->shouldReceive('getUsers')->andReturn(new ArrayCollection([$mockUser]));

        $this->assertEquals(0, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }
}

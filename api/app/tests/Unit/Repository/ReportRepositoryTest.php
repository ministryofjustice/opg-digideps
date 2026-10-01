<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use OPG\Digideps\Backend\Domain\Report\ReportAccessService;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\Search\ClientSearchFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReportRepositoryTest extends TestCase
{
    private Report&MockObject $mockReport;
    private EntityManagerInterface&MockObject $mockEm;
    private ReportRepository $sut;

    public function setUp(): void
    {
        $mockMetaClass = self::createMock(ClassMetadata::class);

        $this->mockEm = self::createConfiguredMock(EntityManagerInterface::class, ['getClassMetadata' => $mockMetaClass]);

        $mockManagerRegistry = self::createConfiguredMock(ManagerRegistry::class, ['getManagerForClass' => $this->mockEm]);

        $clientSearchFilter = self::createMock(ClientSearchFilter::class);

        $mockClient = self::createMock(Client::class);
        $this->mockReport = self::createConfiguredMock(Report::class, ['getClient' => $mockClient]);

        $this->sut = new ReportRepository(
            $mockManagerRegistry,
            $clientSearchFilter,
            new ReportAccessService($this->createStub(EntityManagerInterface::class))
        );
    }

    /**
     * @throws ORMException
     */
    public function testAddFeesToReportIfMissingForNonPAUser(): void
    {
        $this->mockReport->expects(self::once())->method('isPAReport')->willReturn(false);

        $this->assertNull($this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesMissing(): void
    {
        $this->mockReport->expects(self::once())->method('getFees')->willReturn(new ArrayCollection([]));

        $this->mockReport->expects(self::exactly(count(Fee::$feeTypeIds)))->method('addFee')->willReturnSelf();

        $this->mockReport->expects(self::once())->method('isPAReport')->willReturn(true);

        $this->assertEquals(7, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesNotMissing(): void
    {
        $this->mockReport->expects(self::once())->method('getFees')->willReturn(new ArrayCollection(['foo']));
        $this->mockReport->expects(self::once())->method('isPAReport')->willReturn(true);

        $this->mockReport->expects(self::never())->method('addFee');
        $this->mockEm->expects(self::never())->method('persist');

        $this->assertEquals(0, $this->sut->addFeesToReportIfMissing($this->mockReport));
    }
}

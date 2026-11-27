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
use PHPUnit\Framework\TestCase;

final class ReportRepositoryTest extends TestCase
{
    /**
     * @throws ORMException
     */
    public function testAddFeesToReportIfMissingForNonPAUser(): void
    {
        $stubMetaClass = self::createStub(ClassMetadata::class);

        $stubEm = self::createConfiguredStub(EntityManagerInterface::class, ['getClassMetadata' => $stubMetaClass]);

        $stubManagerRegistry = self::createConfiguredStub(ManagerRegistry::class, ['getManagerForClass' => $stubEm]);

        $clientSearchFilter = self::createStub(ClientSearchFilter::class);

        $stubClient = self::createStub(Client::class);
        $mockReport = self::createConfiguredMock(Report::class, ['getClient' => $stubClient]);

        $sut = new ReportRepository(
            $stubManagerRegistry,
            $clientSearchFilter,
            new ReportAccessService($this->createStub(EntityManagerInterface::class))
        );

        $mockReport->expects(self::once())->method('isPAReport')->willReturn(false);

        $this->assertNull($sut->addFeesToReportIfMissing($mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesMissing(): void
    {
        $stubMetaClass = self::createStub(ClassMetadata::class);

        $stubEm = self::createConfiguredStub(EntityManagerInterface::class, ['getClassMetadata' => $stubMetaClass]);

        $stubManagerRegistry = self::createConfiguredStub(ManagerRegistry::class, ['getManagerForClass' => $stubEm]);

        $clientSearchFilter = self::createStub(ClientSearchFilter::class);

        $stubClient = self::createStub(Client::class);
        $mockReport = self::createConfiguredMock(Report::class, ['getClient' => $stubClient]);

        $sut = new ReportRepository(
            $stubManagerRegistry,
            $clientSearchFilter,
            new ReportAccessService($this->createStub(EntityManagerInterface::class))
        );

        $mockReport->expects(self::once())->method('getFees')->willReturn(new ArrayCollection([]));

        $mockReport->expects(self::exactly(count(Fee::$feeTypeIds)))->method('addFee')->willReturnSelf();

        $mockReport->expects(self::once())->method('isPAReport')->willReturn(true);

        $this->assertEquals(7, $sut->addFeesToReportIfMissing($mockReport));
    }

    public function testAddFeesToReportIfMissingForPAUserWithFeesNotMissing(): void
    {
        $stubMetaClass = self::createStub(ClassMetadata::class);

        $mockEm = self::createConfiguredMock(EntityManagerInterface::class, ['getClassMetadata' => $stubMetaClass]);

        $stubManagerRegistry = self::createConfiguredStub(ManagerRegistry::class, ['getManagerForClass' => $mockEm]);

        $clientSearchFilter = self::createStub(ClientSearchFilter::class);

        $stubClient = self::createStub(Client::class);
        $mockReport = self::createConfiguredMock(Report::class, ['getClient' => $stubClient]);

        $sut = new ReportRepository(
            $stubManagerRegistry,
            $clientSearchFilter,
            new ReportAccessService($this->createStub(EntityManagerInterface::class))
        );

        $mockReport->expects(self::once())->method('getFees')->willReturn(new ArrayCollection(['foo']));
        $mockReport->expects(self::once())->method('isPAReport')->willReturn(true);

        $mockReport->expects(self::never())->method('addFee');
        $mockEm->expects(self::never())->method('persist');

        $this->assertEquals(0, $sut->addFeesToReportIfMissing($mockReport));
    }
}

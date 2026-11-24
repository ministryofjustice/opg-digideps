<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OPG\Digideps\Backend\Domain\Report\ReportAccessService;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Fee;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\Search\ClientSearchFilter;
use OPG\Digideps\Common\Report\ReportReplacement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ReportRepositoryTest extends TestCase
{
    private const int REPLACE_REPORT_ID = 10;
    private const int WITH_REPORT_ID = 20;

    private Report&MockObject $mockReport;
    private EntityManagerInterface&MockObject $mockEm;
    private ReportRepository $sut;
    private bool $inTransaction = false;

    public function setUp(): void
    {
        $mockMetaClass = self::createMock(ClassMetadata::class);
        $mockMetaClass->name = Report::class;

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

    /**
     * @return iterable<string, array{array<ReportReplacement>}>
     */
    public static function possibleReplacementsIncludingRequestedProvider(): iterable
    {
        yield 'only possible replacement' => [
            [new ReportReplacement(self::REPLACE_REPORT_ID, self::WITH_REPORT_ID)],
        ];
        yield 'one of several possible replacements' => [
            [new ReportReplacement(30, 40), new ReportReplacement(self::REPLACE_REPORT_ID, self::WITH_REPORT_ID)],
        ];
    }

    /**
     * @param array<ReportReplacement> $possibleReplacements
     */
    #[DataProvider('possibleReplacementsIncludingRequestedProvider')]
    public function testRestoreReportSwapsCourtOrdersAndUpdatesRestoredReportTypeWhenReplacementIsPossible(array $possibleReplacements): void
    {
        $client = self::createConfiguredStub(Client::class, ['findPossibleReportReplacements' => $possibleReplacements]);
        $replaceReport = $this->createReportForClient($client);
        $withReport = $this->createReportForClient($client);
        $replacement = new ReportReplacement(self::REPLACE_REPORT_ID, self::WITH_REPORT_ID);
        $this->expectLockedReportQueryInTransaction($replacement, [
            self::REPLACE_REPORT_ID => $replaceReport,
            self::WITH_REPORT_ID => $withReport,
        ]);

        $replaceReport->method('getType')->willReturn('102');
        $replaceReport->expects(self::once())->method('swapCourtOrdersWith')->with(self::identicalTo($withReport));
        $replaceReport->expects(self::never())->method('setType');
        $replaceReport->expects(self::never())->method('updateSectionsStatusCache');
        $withReport->expects(self::never())->method('swapCourtOrdersWith');
        $withReport->expects(self::once())->method('setType')->with('102')->willReturnSelf();
        $withReport->expects(self::once())->method('updateSectionsStatusCache');

        self::assertTrue($this->sut->restoreReport($replacement));
    }

    /**
     * @return iterable<string, array{int, int, array<int>}>
     */
    public static function missingReportProvider(): iterable
    {
        yield 'neither report found' => [self::REPLACE_REPORT_ID, self::WITH_REPORT_ID, []];
        yield 'report to replace not found' => [self::REPLACE_REPORT_ID, self::WITH_REPORT_ID, [self::WITH_REPORT_ID]];
        yield 'report to restore not found' => [self::REPLACE_REPORT_ID, self::WITH_REPORT_ID, [self::REPLACE_REPORT_ID]];
        yield 'same report requested twice' => [self::REPLACE_REPORT_ID, self::REPLACE_REPORT_ID, [self::REPLACE_REPORT_ID]];
    }

    /**
     * @param array<int> $foundReportIds
     */
    #[DataProvider('missingReportProvider')]
    public function testRestoreReportReturnsFalseUnlessBothReportsAreFound(int $replaceReportId, int $withReportId, array $foundReportIds): void
    {
        $client = self::createMock(Client::class);
        $client->expects(self::never())->method('findPossibleReportReplacements');

        $reports = [];
        foreach ($foundReportIds as $id) {
            $reports[$id] = $this->createReportForClient($client);
            $this->expectReportUnchanged($reports[$id]);
        }

        $replacement = new ReportReplacement($replaceReportId, $withReportId);
        $this->expectLockedReportQueryInTransaction($replacement, $reports);

        self::assertFalse($this->sut->restoreReport($replacement));
    }

    public function testRestoreReportReturnsFalseWhenReportsBelongToDifferentClients(): void
    {
        $replaceReportClient = self::createMock(Client::class);
        $replaceReportClient->expects(self::never())->method('findPossibleReportReplacements');
        $withReportClient = self::createMock(Client::class);
        $withReportClient->expects(self::never())->method('findPossibleReportReplacements');

        $replaceReport = $this->createReportForClient($replaceReportClient);
        $this->expectReportUnchanged($replaceReport);
        $withReport = $this->createReportForClient($withReportClient);
        $this->expectReportUnchanged($withReport);

        $replacement = new ReportReplacement(self::REPLACE_REPORT_ID, self::WITH_REPORT_ID);
        $this->expectLockedReportQueryInTransaction($replacement, [
            self::REPLACE_REPORT_ID => $replaceReport,
            self::WITH_REPORT_ID => $withReport,
        ]);

        self::assertFalse($this->sut->restoreReport($replacement));
    }

    /**
     * @return iterable<string, array{array<ReportReplacement>}>
     */
    public static function possibleReplacementsExcludingRequestedProvider(): iterable
    {
        yield 'no possible replacements' => [[]];
        yield 'reports the other way round' => [
            [new ReportReplacement(self::WITH_REPORT_ID, self::REPLACE_REPORT_ID)],
        ];
        yield 'report to replace can only be replaced by a different report' => [
            [new ReportReplacement(self::REPLACE_REPORT_ID, 30)],
        ];
        yield 'report to restore can only replace a different report' => [
            [new ReportReplacement(30, self::WITH_REPORT_ID)],
        ];
    }

    /**
     * @param array<ReportReplacement> $possibleReplacements
     */
    #[DataProvider('possibleReplacementsExcludingRequestedProvider')]
    public function testRestoreReportReturnsFalseWhenReplacementIsNotPossible(array $possibleReplacements): void
    {
        $client = self::createConfiguredStub(Client::class, ['findPossibleReportReplacements' => $possibleReplacements]);
        $replaceReport = $this->createReportForClient($client);
        $this->expectReportUnchanged($replaceReport);
        $withReport = $this->createReportForClient($client);
        $this->expectReportUnchanged($withReport);

        $replacement = new ReportReplacement(self::REPLACE_REPORT_ID, self::WITH_REPORT_ID);
        $this->expectLockedReportQueryInTransaction($replacement, [
            self::REPLACE_REPORT_ID => $replaceReport,
            self::WITH_REPORT_ID => $withReport,
        ]);

        self::assertFalse($this->sut->restoreReport($replacement));
    }

    private function createReportForClient(Client $client): Report&MockObject
    {
        $report = self::createMock(Report::class);
        $report->method('getClient')->willReturn($client);

        return $report;
    }

    private function expectReportUnchanged(Report&MockObject $report): void
    {
        $report->expects(self::never())->method('swapCourtOrdersWith');
        $report->expects(self::never())->method('setType');
        $report->expects(self::never())->method('updateSectionsStatusCache');
    }

    /**
     * Expect both reports to be fetched, indexed by ID, with a write lock inside a single transaction.
     *
     * @param array<int, Report> $result
     */
    private function expectLockedReportQueryInTransaction(ReportReplacement $replacement, array $result): void
    {
        $this->mockEm->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function (callable $func): mixed {
                $this->inTransaction = true;
                try {
                    return $func($this->mockEm);
                } finally {
                    $this->inTransaction = false;
                }
            });

        $this->mockEm->method('createQueryBuilder')->willReturnCallback(fn () => new QueryBuilder($this->mockEm));

        $query = self::createMock(Query::class);
        $query->expects(self::once())
            ->method('setParameters')
            ->with(self::callback(function (ArrayCollection $parameters) use ($replacement): bool {
                $ids = $parameters->first();

                return count($parameters) === 1
                    && $ids instanceof Parameter
                    && $ids->getName() === 'ids'
                    && $ids->getValue() === [$replacement->replaceReport, $replacement->withReport];
            }))
            ->willReturnSelf();
        $query->method('setFirstResult')->willReturnSelf();
        $query->method('setMaxResults')->willReturnSelf();
        $query->expects(self::once())->method('getResult')->willReturn($result);

        $this->mockEm->expects(self::once())
            ->method('createQuery')
            ->with('SELECT r FROM ' . Report::class . ' r INDEX BY r.id WHERE r.id IN (:ids) ORDER BY r.id ASC')
            ->willReturnCallback(function () use ($query): Query {
                self::assertTrue($this->inTransaction, 'Reports must be fetched and locked inside the transaction');

                return $query;
            });
    }
}

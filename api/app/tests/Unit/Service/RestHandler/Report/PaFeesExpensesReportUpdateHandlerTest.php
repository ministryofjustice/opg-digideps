<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\RestHandler\Report;

use PHPUnit\Framework\MockObject\MockObject;
use DateTime;
use DateTimeZone;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use App\Service\RestHandler\Report\PaFeesExpensesReportUpdateHandler;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

final class PaFeesExpensesReportUpdateHandlerTest extends TestCase
{
    private PaFeesExpensesReportUpdateHandler $sut;
    private EntityManager&MockObject $em;
    private Report&MockObject $report;
    private ReportRepository&MockObject $reportRepo;

    public function setUp(): void
    {
        $date = new DateTime('now', new DateTimeZone('Europe/London'));
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, $date, $date])
            ->onlyMethods(['updateSectionsStatusCache'])
            ->getMock();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportRepo = $this->getMockBuilder(ReportRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFeesToReportIfMissing'])
            ->getMock();

        $this->em->expects($this->any())
            ->method('getRepository')
            ->with(Report::class)
            ->willReturn($this->reportRepo);

        $this->sut = new PaFeesExpensesReportUpdateHandler($this->em);
    }

    /**
     * Fees only get created when reason is empty.
     */
    public function testInitialiseFeesGetCreated(): void
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->reportRepo->expects($this->once())
            ->method('addFeesToReportIfMissing')
            ->with($this->report);

        $this->invokeHandler(['reason_for_no_fees' => null]);
    }

    /**
     * Fees dont get created when therres a reason for no fees.
     */
    public function testInitialiseFeesDontGetCreated(): void
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->reportRepo->expects($this->never())
            ->method('addFeesToReportIfMissing')
            ->with($this->report);

        $this->invokeHandler(['reason_for_no_fees' => 'some reason']);
    }

    private function invokeHandler(array $data): void
    {
        $this->sut->handle($this->report, $data);
    }

    private function ensureSectionStatusCacheWillBeUpdated(): void
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PA_DEPUTY_EXPENSES]);
    }
}

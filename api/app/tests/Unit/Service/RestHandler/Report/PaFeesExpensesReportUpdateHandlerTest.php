<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Service\RestHandler\Report;

use Doctrine\ORM\EntityManager;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\RestHandler\Report\PaFeesExpensesReportUpdateHandler;
use PHPUnit\Framework\TestCase;

final class PaFeesExpensesReportUpdateHandlerTest extends TestCase
{
    /**
     * Fees only get created when reason is empty.
     */
    public function testInitialiseFeesGetCreated(): void
    {
        $report = $this->createMock(Report::class);
        $report->expects($this->once())->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PA_DEPUTY_EXPENSES]);
        $reportRepo = $this->createMock(ReportRepository::class);
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Report::class)
            ->willReturn($reportRepo);
        $reportRepo->expects($this->once())
            ->method('addFeesToReportIfMissing')
            ->with($report);

        $sut = new PaFeesExpensesReportUpdateHandler($em);
        $sut->handle($report, ['reason_for_no_fees' => null]);
    }

    /**
     * Fees dont get created when therres a reason for no fees.
     */
    public function testInitialiseFeesDontGetCreated(): void
    {
        $report = $this->createMock(Report::class);
        $report->expects($this->once())->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PA_DEPUTY_EXPENSES]);
        $reportRepo = $this->createMock(ReportRepository::class);
        $reportRepo->expects($this->never())->method('addFeesToReportIfMissing');
        $em = $this->createStub(EntityManager::class);
        $em->method('getRepository')->willReturn($reportRepo);

        $sut = new PaFeesExpensesReportUpdateHandler($em);
        $sut->handle($report, ['reason_for_no_fees' => 'some reason']);
    }
}

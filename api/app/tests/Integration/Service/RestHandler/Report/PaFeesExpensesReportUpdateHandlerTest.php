<?php

namespace App\Tests\Integration\Service\RestHandler\Report;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Repository\ReportRepository;
use App\Service\RestHandler\Report\PaFeesExpensesReportUpdateHandler;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class PaFeesExpensesReportUpdateHandlerTest extends TestCase
{
    /** @var PaFeesExpensesReportUpdateHandler */
    private $sut;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var Report|\PHPUnit_Framework_MockObject_MockObject */
    private $report;

    /** @var ReportRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $reportRepo;

    public function setUp(): void
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client(), Report::LAY_PFA_HIGH_ASSETS_TYPE, $date, $date])
            ->setMethods(['updateSectionsStatusCache'])
            ->getMock();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportRepo = $this->getMockBuilder(ReportRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFeesToReportIfMissing'])
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
    public function testInitialiseFeesGetCreated()
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
    public function testInitialiseFeesDontGetCreated()
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->reportRepo->expects($this->never())
            ->method('addFeesToReportIfMissing')
            ->with($this->report);

        $this->invokeHandler(['reason_for_no_fees' => 'some reason']);
    }

    private function invokeHandler(array $data)
    {
        $this->sut->handle($this->report, $data);
    }

    private function ensureSectionStatusCacheWillBeUpdated()
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PA_DEPUTY_EXPENSES]);
    }
}

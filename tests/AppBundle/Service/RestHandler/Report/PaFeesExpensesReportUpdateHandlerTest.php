<?php

namespace Tests\AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\ProfDeputyEstimateCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Service\RestHandler\Report\PaFeesExpensesReportUpdateHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class PaFeesExpensesReportUpdateHandlerTest extends TestCase
{
    /** @var PaFeesExpensesReportUpdateHandler */
    private $sut;

    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject */
    private $em;

    /** @var Report | \PHPUnit_Framework_MockObject_MockObject */
    private $report;

    /** @var ReportRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $reportRepo;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $date = new \DateTime('now', new \DateTimeZone('Europe/London'));
        $this->report = $this->getMockBuilder(Report::class)
            ->setConstructorArgs([new Client, Report::TYPE_102, $date, $date])
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
     * Fees only get created when reason is empty
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
     * Fees dont get created when therres a reason for no fees
     */
    public function testInitialiseFeesDontGetCreated()
    {
        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->reportRepo->expects($this->never())
            ->method('addFeesToReportIfMissing')
            ->with($this->report);

        $this->invokeHandler(['reason_for_no_fees' => 'some reason']);

    }

    /**
     * @param array $data
     */
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
            ->with([Report::SECTION_PA_DEPUTY_EXPENSES ]);
    }

}

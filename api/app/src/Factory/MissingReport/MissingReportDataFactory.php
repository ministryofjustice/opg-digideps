<?php

declare(strict_types=1);

namespace App\Factory\MissingReport;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Entity\CourtOrder;
use App\Entity\Report\Report;
use App\Factory\DataFactoryInterface;
use App\Factory\DataFactoryResult;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MissingReportDataFactory implements DataFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private MissingReportFinder $finder,
        private ReportService $reportService,
    ) {
    }

    public function getName(): string
    {
        return 'MissingReport';
    }

    public function run(): DataFactoryResult
    {
        $errors = [];
        $created = 0;
        $linked = 0;

        foreach ($this->finder->findCourtOrdersWithMissingReports() as $courtOrder) {
            try {
                if ($this->linkHybridReportIfItExists($courtOrder)) {
                    $linked++;
                } else {
                    $this->createMissingReport($courtOrder);
                    $created++;
                }
            } catch (\Throwable $throwable) {
                $class = $throwable::class;
                $errors[] = "Could not generate missing report for court order: {$courtOrder->getCourtOrderUid()}: {$class} {$throwable->getMessage()} in {$throwable->getFile()}({$throwable->getLine()})";
            }
        }

        return new DataFactoryResult(['success' => ["Created {$created} missing reports", "Linked {$linked} unlinked reports"]], ['errors' => $errors]);
    }

    private function createMissingReport(CourtOrder $courtOrder): void
    {
        $latest = $this->getLatestReport($courtOrder);
        $newReport = $latest !== null ? $this->createReportFromReport($latest) : $this->createReportFromOrder($courtOrder);
        $courtOrder->addReport($newReport);
        $this->em->persist($courtOrder);
        $this->em->flush();
    }

    private function linkHybridReportIfItExists(CourtOrder $courtOrder): bool
    {
        if ($courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
            $siblingReport = $courtOrder->getSibling()?->getLatestReport();
            if ($siblingReport !== null && empty($siblingReport->getSubmitted())) {
                $courtOrder->addReport($siblingReport);
                $this->em->persist($courtOrder);
                $this->em->flush();
                return true;
            }
        }
        return false;
    }

    private function getLatestReport(CourtOrder $courtOrder): ?Report
    {
        $latest = $courtOrder->getLatestReport();
        if ($latest === null && $courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
            $latest = $courtOrder->getSibling()?->getLatestReport();

            if ($latest !== null && empty($latest->getSubmitted())) {
                throw new \LogicException('We already checked for a missing link in linkHybridReportIfItExists() so this should never happen.');
            }
        }
        return $latest;
    }

    private function createReportFromOrder(CourtOrder $courtOrder): Report
    {
        $newReport = new Report(
            $courtOrder->getClient(),
            $courtOrder->getDesiredReportType(),
            $courtOrder->getOrderMadeDate(),
            (clone $courtOrder->getOrderMadeDate())->modify('+12 months -1 day'),
            false,
        );
        $newReport->updateSectionsStatusCache($newReport->getAvailableSections());
        $this->em->persist($newReport);
        return $newReport;
    }

    private function createReportFromReport(Report $latest): Report
    {
        return $this->reportService->createNextYearReport($latest);
    }
}

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
        $count = 0;
        foreach ($this->finder->findCourtOrdersWithMissingReports() as $courtOrder) {
            $result = $this->createReport($courtOrder);
            if ($result === null) {
                $count++;
            } else {
                $errors[] = $result;
            }
        }

        return new DataFactoryResult(['Success' => ["Created {$count} missing reports"]], ['Errors' => $errors]);
    }

    private function createReport(CourtOrder $courtOrder): ?string
    {
        try {
            $latest = $courtOrder->getLatestReport();
            if ($courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
                $siblingReport = $courtOrder->getSibling()?->getLatestReport();
                if ($siblingReport !== null && empty($siblingReport->getSubmitted())) {
                    $courtOrder->addReport($siblingReport);
                    $this->em->flush();
                    return null;
                }
                $latest ??= $siblingReport;
            }

            $newReport = $latest !== null ? $this->reportService->createNextYearReport($latest) : new Report(
                $courtOrder->getClient(),
                $courtOrder->getDesiredReportType(),
                $courtOrder->getOrderMadeDate(),
                (clone $courtOrder->getOrderMadeDate())->modify('+12 months -1 day'),
                false,
            );
            $courtOrder->addReport($newReport);
            $this->em->flush();
        } catch (\Throwable $throwable) {
            $class = $throwable::class;
            return "Could not generate missing report for court order: {$courtOrder->getCourtOrderUid()}: {$class} {$throwable->getMessage()} in {$throwable->getFile()}({$throwable->getLine()})";
        }
        return null;
    }
}

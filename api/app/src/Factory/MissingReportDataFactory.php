<?php

declare(strict_types=1);

namespace App\Factory;

use App\Domain\CourtOrder\CourtOrderKind;
use App\Entity\CourtOrder;
use App\Entity\Report\Report;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MissingReportDataFactory implements DataFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReportService $reportService,
    ) {
    }

    public function getName(): string
    {
        return 'MissingReport';
    }

    /**
     * @return \Generator<CourtOrder>
     */
    private function findCourtOrdersWithMissingReports(): \Generator
    {
        $repository = $this->em->getRepository(CourtOrder::class);
        foreach (
            $this->em->getConnection()->executeQuery("
            SELECT DISTINCT co.id
            FROM court_order co
            LEFT JOIN court_order_report cor
                ON co.id = cor.court_order_id
            LEFT JOIN report r
                ON r.id = cor.report_id
            WHERE
                NOT r.submitted
                AND co.status <> 'ACTIVE'
                AND r.id IS NULL
            ")->iterateColumn() as $courtOrderId
        ) {
            $order = $repository->find($courtOrderId);
            if ($order !== null) {
                yield $order;
            }
        }
    }

    public function run(): DataFactoryResult
    {
        $errors = [];
        $count = 0;
        foreach ($this->findCourtOrdersWithMissingReports() as $courtOrder) {
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
                $latest ??= $courtOrder->getSibling()?->getLatestReport();
            }

            $newReport = $latest !== null ? $this->reportService->createNextYearReport($latest) : new Report(
                $courtOrder->getClient(),
                $courtOrder->getDesiredReportType(),
                $courtOrder->getOrderMadeDate(),
                (clone $courtOrder->getOrderMadeDate())->modify('+12 months -1 day'),
                false,
            );
            if ((!$newReport instanceof Report)) {
                return "Could not generate missing report for court order: {$courtOrder->getCourtOrderUid()}";
            }
            $courtOrder->addReport($newReport);
            $this->em->flush();
        } catch (\Throwable $throwable) {
            $class = $throwable::class;
            return "{$class} {$throwable->getMessage()} in {$throwable->getFile()}({$throwable->getLine()})";
        }
        return null;
    }
}

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
use Psr\Log\LoggerInterface;

final readonly class MissingReportDataFactory implements DataFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private MissingReportFinder $finder,
        private ReportService $reportService,
        private LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'MissingReport';
    }

    public function run(bool $dryRun = true): DataFactoryResult
    {
        $errors = [];
        $created = 0;
        $linked = 0;

        foreach ($this->finder->findCourtOrdersWithMissingReports() as $courtOrder) {
            try {
                if ($this->linkHybridReportIfItExists($courtOrder, $dryRun)) {
                    $linked++;
                } else {
                    $this->createMissingReport($courtOrder, $dryRun);
                    $created++;
                }
            } catch (\Throwable $throwable) {
                $class = $throwable::class;
                $errors[] = "Could not generate missing report for court order: {$courtOrder->getCourtOrderUid()}: {$class} {$throwable->getMessage()} in {$throwable->getFile()}({$throwable->getLine()})";
            }
        }

        $dry = $dryRun ? '[Dry run] ' : '';
        return new DataFactoryResult(['success' => ["{$dry}Created {$created} missing reports", "{$dry}Linked {$linked} unlinked reports"]], ['errors' => $errors]);
    }

    private function log(string $message, bool $dryRun): void
    {
        $dry = $dryRun ? '[Dry run] ' : '';
        $this->logger->info("{$this->getName()}: {$dry}{$message}");
    }

    private function createMissingReport(CourtOrder $courtOrder, bool $dryRun): void
    {
        $latest = $this->getLatestReport($courtOrder);
        $this->log('Creating report ' . ($latest === null ? '' : "from report {$latest->getType()} ") . "for court order {$courtOrder->getId()}.", $dryRun);

        if ($dryRun) {
            return;
        }

        $newReport = $latest === null ? $this->createReportFromOrder($courtOrder) : $this->createReportFromReport($latest);
        $courtOrder->addReport($newReport);
        $this->em->persist($courtOrder);
        $this->em->flush();
    }

    private function linkHybridReportIfItExists(CourtOrder $courtOrder, bool $dryRun): bool
    {
        if ($courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
            $siblingReport = $courtOrder->getSibling()?->getLatestReport();
            if ($siblingReport !== null && empty($siblingReport->getSubmitted())) {
                $this->log("Linking report {$siblingReport->getId()} to hybrid court order {$courtOrder->getId()}.", $dryRun);
                if (!$dryRun) {
                    $courtOrder->addReport($siblingReport);
                    $this->em->persist($courtOrder);
                    $this->em->flush();
                }
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
            "{$courtOrder->getDesiredReportType()}",
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
        return $this->reportService->createNextYearReport($latest, false);
    }
}

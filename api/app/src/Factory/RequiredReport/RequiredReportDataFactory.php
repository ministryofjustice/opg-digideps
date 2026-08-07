<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory\RequiredReport;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Factory\DataFactoryInterface;
use OPG\Digideps\Backend\Factory\DataFactoryResult;
use OPG\Digideps\Backend\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final readonly class RequiredReportDataFactory implements DataFactoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequiredReportFinder $finder,
        private ReportService $reportService,
        private LoggerInterface $verboseLogger,
    ) {
    }

    public function getName(): string
    {
        return 'RequiredReport';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $errors = [];
        $created = 0;
        $linked = 0;

        foreach ($this->finder->findCourtOrdersWithoutRequiredReports() as $courtOrder) {
            try {
                if ($this->linkHybridReportIfItExists($courtOrder, $dryRun)) {
                    $linked++;
                } else {
                    $this->createRequiredReport($courtOrder, $dryRun);
                    $created++;
                }
            } catch (\Throwable $throwable) {
                $class = $throwable::class;
                $errors[] = "Could not generate required report for court order: {$courtOrder->getCourtOrderUid()}: {$class} {$throwable->getMessage()} in {$throwable->getFile()}({$throwable->getLine()})";
            }
            $this->em->clear();
        }

        $dry = $dryRun ? '[Dry run] ' : '';
        return new DataFactoryResult(['success' => ["{$dry}Created {$created} required reports", "{$dry}Linked {$linked} unlinked reports"]], ['errors' => $errors]);
    }

    private function log(string $message, bool $dryRun): void
    {
        $dry = $dryRun ? '[Dry run] ' : '';
        $this->verboseLogger->notice("{$this->getName()}: {$dry}{$message}");
    }

    private function createRequiredReport(CourtOrder $courtOrder, bool $dryRun): void
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
        $newReport = $this->reportService->createReportFromOrder($courtOrder);
        $this->em->persist($newReport);
        return $newReport;
    }

    private function createReportFromReport(Report $latest): Report
    {
        return $this->reportService->createNextYearReport($latest, false);
    }
}

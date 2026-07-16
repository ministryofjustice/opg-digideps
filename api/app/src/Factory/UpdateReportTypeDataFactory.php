<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\ReportTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class UpdateReportTypeDataFactory implements DataFactoryInterface
{
    public function __construct(
        public EntityManagerInterface $entityManager,
        public ReportRepository $reportRepository,
        public LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'ReportTypeUpdate';
    }

    /**
     * @return \Generator<int>
     */
    private function getAllReportIdsOnActiveCourtOrders(): \Generator
    {
        $result = $this->entityManager->getConnection()->executeQuery(<<<SQL
            SELECT DISTINCT r.id FROM report r
            INNER JOIN court_order_report cor ON cor.report_id = r.id
            INNER JOIN court_order co ON co.id = cor.court_order_id
            WHERE co.status = 'ACTIVE'
        SQL);

        foreach ($result->iterateColumn() as $reportId) {
            if (is_int($reportId)) {
                yield $reportId;
            }
        }
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $count = 0;
        $indeterminate = [];
        $dangerous = [];
        $reportRepository = $this->entityManager->getRepository(Report::class);

        foreach ($this->getAllReportIdsOnActiveCourtOrders() as $reportId) {
            $report = $reportRepository->find($reportId) ?? throw new \LogicException("Report with id {$reportId} is proven to exist.");

            $courtOrders = $report->getActiveCourtOrders();

            $currentReportType = ReportType::tryFrom($report->getType());
            $possibleReportType = ReportTypeService::determineReportType($courtOrders);

            if ((string) $currentReportType === (string) $possibleReportType) {
                continue;
            }

            // ignore if we couldn't figure out a valid report type
            if ($possibleReportType === null) {
                $indeterminate[] = $reportId;
                continue;
            }

            // ignore hybrid <-> separate reports(s) transitions
            if (
                $currentReportType !== null &&
                (
                    $currentReportType->courtOrderKind === CourtOrderKind::Hybrid ||
                    $possibleReportType->courtOrderKind === CourtOrderKind::Hybrid
                )
            ) {
                $dangerous[] = $reportId;
                continue;
            }

            if (!$dryRun) {
                $report->setType((string) $possibleReportType);
                $this->entityManager->persist($report);
                ++$count;

                if ($count % 128 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            } else {
                $this->logger->info(
                    "DRYRUN[{$this->getName()}]: Report with ID: $reportId; report type change from $currentReportType to $possibleReportType"
                );
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $messages = ['success' => ["Updated $count report types"]];

        // don't treat indeterminate or dangerous report type transitions as errors which will stop the ingest
        $numIndeterminate = count($indeterminate);
        if ($numIndeterminate > 0) {
            $messages['indeterminate'] = ["Unable to determine report type for $numIndeterminate report IDs: " . implode(', ', $indeterminate)];
        }

        $numDangerous = count($dangerous);
        if ($numDangerous > 0) {
            $messages['dangerous'] = ["Possible dangerous change of report type to/from hybrid for $numDangerous report IDs: " . implode(', ', $dangerous)];
        }

        return new DataFactoryResult(
            messages: $messages,
            errorMessages: ['errors' => []]
        );
    }
}

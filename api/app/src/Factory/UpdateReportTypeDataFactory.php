<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\ReportTypeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
     * @return \Generator<Report>
     */
    private function getAllReportsOnActiveCourtOrders(): \Generator
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(Report::class, 'r');

        /** @var Report[] $result */
        $result = $this->entityManager->createNativeQuery(<<<SQL
            SELECT DISTINCT r.* FROM report r
            INNER JOIN court_order_report cor ON cor.report_id = r.id
            INNER JOIN court_order co ON co.id = cor.court_order_id
            WHERE co.status = 'ACTIVE'
        SQL, $rsm)->getResult();

        foreach ($result as $report) {
            yield $report;
        }
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $count = 0;
        $indeterminate = [];
        $dangerous = [];

        foreach ($this->getAllReportsOnActiveCourtOrders() as $report) {
            $reportId = $report->getId();

            /** @var CourtOrder[] $courtOrders */
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
                }
            } else {
                $this->logger->info(
                    "DRYRUN[{$this->getName()}]: Report with ID: $reportId; report type change from $currentReportType to $possibleReportType"
                );
            }
        }

        $this->entityManager->flush();

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

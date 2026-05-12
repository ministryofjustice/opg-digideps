<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\ReportTypeService;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Psr\Log\LoggerInterface;

readonly class ReportTypeUpdateFactory implements DataFactoryInterface
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
    private function getChangedReports(): \Generator
    {
        $actionsNeeded = [
            DeputyshipCandidateAction::InsertOrderDeputy->value,
            DeputyshipCandidateAction::InsertOrderReport->value,
            DeputyshipCandidateAction::UpdateDeputyStatus->value
        ];

        $sql = <<<SQL
        SELECT DISTINCT r.id
        FROM court_order co
        INNER JOIN staging.selectedcandidates ssc ON ssc.order_uid = co.court_order_uid
        INNER JOIN court_order_report cor ON cor.court_order_id = co.id
        INNER JOIN report r ON r.id = cor.report_id
        LEFT JOIN report_submission rs ON rs.report_id = r.id
        WHERE
            ssc.action IN (:actions)
        AND
            rs.id IS NULL
        SQL;

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addScalarResult('id', 'id');
        $reportIds = $this->entityManager
            ->createNativeQuery($sql, $rsm)
            ->setParameters(['actions' => $actionsNeeded]);

        foreach ($reportIds->toIterable() as $reportId) {
            /** @var Report $report */
            $report = $this->reportRepository->find($reportId);

            yield $report;
        }
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        $count = 0;
        $indeterminate = [];
        $dangerous = [];

        foreach ($this->getChangedReports() as $report) {
            $reportId = $report->getId();

            /** @var CourtOrder[] $courtOrders */
            $courtOrders = $report->getActiveCourtOrders();

            $currentReportType = ReportType::tryFrom($report->getType());
            $possibleReportType = ReportTypeService::determineReportType($courtOrders);

            if ($possibleReportType === null) {
                $indeterminate[] = $reportId;
                continue;
            }

            if ((string) $currentReportType === (string) $possibleReportType) {
                continue;
            }

            if (
                $currentReportType !== null &&
                (
                    $currentReportType->courtOrderKind === CourtOrderKind::Hybrid ||
                    $possibleReportType->courtOrderKind === CourtOrderKind::Hybrid
                )
            ) {
                $dangerous[] = $reportId;
            }

            if (!$dryRun) {
                $report->setType((string) $possibleReportType);
                $this->entityManager->persist($report);
                ++$count;
            } else {
                $this->logger->info(
                    "DRYRUN[{$this->getName()}]: Report with ID: $reportId; report type change from $currentReportType to $possibleReportType"
                );
            }
        }

        $numIndeterminate = count($indeterminate);
        $numDangerous = count($dangerous);

        $errors = [];
        if ($numIndeterminate > 0) {
            $errors[] = "Unable to determine report type for $numIndeterminate report IDs: " . implode(', ', $indeterminate);
        }

        // while we log this as an error, we apply the change to/from hybrid anyway
        if ($numDangerous > 0) {
            $errors[] = "Possible dangerous change of report type to/from hybrid for $numDangerous report IDs: " . implode(', ', $dangerous);
        }

        return new DataFactoryResult(
            messages: ['success' => ["Updated $count report types"]],
            errorMessages: ['errors' => $errors]
        );
    }
}

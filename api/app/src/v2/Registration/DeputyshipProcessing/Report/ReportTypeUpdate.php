<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\Report;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\Report\ReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Factory\DataFactoryResult;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Service\ReportTypeService;
use OPG\Digideps\Backend\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class ReportTypeUpdate
{
    public function __construct(
        public readonly EntityManagerInterface $entityManager,
        public readonly ReportRepository $reportRepository
    ) {
    }

    public function getName(): string
    {
        return 'ReportTypeUpdate';
    }

    /**
     * @return \Generator<Report>
     * @throws QueryException
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

    public function run(): DataFactoryResult
    {
        $count = 0;
        $errors = [];
        foreach ($this->getChangedReports() as $report) {
            /** @var CourtOrder[] $courtOrders */
            $courtOrders = $report->getCourtOrders()->toArray();

            $currentReportType = ReportType::tryFrom($report->getType());
            $possibleReportType = ReportTypeService::determineReportType($courtOrders);

            if ($possibleReportType === null) {
                $errors[] = 'Unable to determine report type from CourtOrders associated with report: ' . $report->getId();
                continue;
            }

            if ((string) $currentReportType === (string) $possibleReportType) {
                continue;
            }

            if (
                $currentReportType !== null && $currentReportType->courtOrderKind === CourtOrderKind::Hybrid ||
                $possibleReportType->courtOrderKind === CourtOrderKind::Hybrid
            ) {
                $errors[] = 'Possible dangerous change to or from Hybrid on report: ' . $report->getId();
            }

            $report->setType($possibleReportType);
            $this->entityManager->persist($report);
            ++$count;
        }

        return new DataFactoryResult(
            messages: ['success' => ["Updated {$count} reportTypes"]],
            errorMessages: ['errors' => $errors]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing;

use App\Entity\StagingSelectedCandidate;
use App\Factory\StagingSelectedCandidateFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

/**
 * For creating candidates for relationships between deputyships and reports.
 * Used to decide which court orders can be associated with which reports.
 * This relies on staging tables populated during deputyships CSV ingest, and should only be used in
 * the context of that process.
 *
 * This uses raw SQL for efficiency, as we'll be processing tens of thousands of rows in the deputyships CSV,
 * but it will be brittle as a consequence.
 */
class CourtOrderReportCandidatesFactory
{
    /** @var string */
    private const SUBSELECT = <<<SQL
        SELECT
            d.order_uid AS court_order_uid,
            d.report_type AS report_type,
            d.order_type AS order_type,
            d.deputy_type AS deputy_type,
            d.order_made_date AS order_made_date,
            r.id AS report_id,

            (
                (d.order_type = 'pfa' AND (r.type = '102' OR r.type = '103'))
                OR
                (d.order_type = 'hw' AND r.type = '104')
                OR
                (
                    (d.order_type = 'pfa' OR d.order_type = 'hw')
                    AND
                    d.is_hybrid = '1'
                    AND
                    (r.type = '102-4' OR r.type = '103-4')
                )
            ) AS report_is_compatible
        FROM staging.deputyship d
        LEFT JOIN client c ON d.case_number = c.case_number
        LEFT JOIN report r ON c.id = r.client_id
    SQL;

    /** @var string */
    private const COMPATIBLE_REPORTS_QUERY = <<<SQL
        SELECT court_order_uid, report_id FROM (
            %s
        ) compat
        WHERE report_is_compatible = true
        GROUP BY court_order_uid, report_id
        ORDER BY court_order_uid, report_id;
    SQL;

    /** @var string */
    private const INCOMPATIBLE_CURRENT_REPORTS_QUERY = <<<SQL
        SELECT court_order_uid, report_type, order_type, deputy_type, order_made_date FROM (
            %s
            WHERE r.id IS NOT NULL AND r.submit_date IS NULL AND r.un_submit_date IS NULL
        ) compat
        WHERE report_is_compatible = false
        GROUP BY court_order_uid;
    SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StagingSelectedCandidateFactory $candidateFactory,
    ) {
    }

    /**
     * Find reports compatible with each ingested deputyship. These become "INSERT COURT ORDER REPORT" candidates.
     * This covers both current and historical reports.
     *
     * A deputyship and existing report are compatible if:
     *
     * deputyship order type == 'pfa' and existing report type == '102' or '103'
     * OR
     * deputyship order type == 'hw' and existing report type == '104'
     * OR
     * deputyship order type == 'pfa' or 'hw' and deputyship is hybrid and existing report type == '102-4' or '103-4'
     *
     * @return StagingSelectedCandidate[] An array of candidate court_order_report inserts
     *
     * @throws Exception
     */
    public function createCompatibleReportCandidates(): array
    {
        $conn = $this->entityManager->getConnection();
        $query = sprintf(self::COMPATIBLE_REPORTS_QUERY, self::SUBSELECT);
        $result = $conn->executeQuery($query)->fetchAllAssociative();

        return array_map(function ($row) {
            return $this->candidateFactory->createInsertOrderReportCandidate(
                ''.$row['order_uid'],
                intval(''.$row['report_id'])
            );
        }, $result);
    }

    /**
     * Find deputyships which have an existing current report which is incompatible with the deputyship's order type.
     * For example, if a dual client already has a pfa report for one court order, and we encounter a
     * second deputyship for the client's other hw court order, we will create a new hw report for that second court order.
     *
     * @return StagingSelectedCandidate[] An array of candidate court_order and court_order_report inserts
     *
     * @throws Exception
     */
    public function createIncompatibleReportCandidates(): array
    {
        $conn = $this->entityManager->getConnection();
        $query = sprintf(self::INCOMPATIBLE_CURRENT_REPORTS_QUERY, self::SUBSELECT);
        $result = $conn->executeQuery($query)->fetchAllAssociative();

        return array_map(function ($row) {
            return $this->candidateFactory->createInsertReportCandidate(
                ''.$row['order_uid'],
                ''.$row['report_type'],
                ''.$row['order_type'],
                ''.$row['deputy_type'],
                ''.$row['order_made_date']
            );
        }, $result);
    }
}

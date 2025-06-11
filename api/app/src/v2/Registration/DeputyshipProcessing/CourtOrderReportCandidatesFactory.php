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
 * For attaching existing reports, we include reports associated with archived and deleted clients, as these need
 * to be attached to the court order (they are still valid reports for that court order).
 *
 * This uses raw SQL for efficiency, as we'll be processing tens of thousands of rows in the deputyships CSV,
 * but it will be brittle as a consequence.
 */
class CourtOrderReportCandidatesFactory
{
    /** @var string */
    private const COMPATIBLE_REPORTS_QUERY = <<<SQL
        SELECT court_order_uid, report_id FROM (
            SELECT
                d.order_uid AS court_order_uid,
                r.id AS report_id,
                (
                    (
                        d.deputy_type = 'LAY'
                        AND (
                            (d.order_type = 'pfa' AND d.is_hybrid = '0' AND r.type IN ('102', '103'))
                            OR
                            (d.order_type = 'hw' AND d.is_hybrid = '0' AND r.type IN ('104'))
                            OR
                            (d.order_type IN ('hw', 'pfa') AND d.is_hybrid = '1' AND r.type IN ('102-4', '103-4'))
                        )
                    )
                    OR
                    (
                        d.deputy_type = 'PA'
                        AND (
                            (d.order_type = 'pfa' AND d.is_hybrid = '0' AND r.type IN ('102-6', '103-6'))
                            OR
                            (d.order_type = 'hw' AND d.is_hybrid = '0' AND r.type IN ('104-6'))
                            OR
                            (d.order_type IN ('hw', 'pfa') AND d.is_hybrid = '1' AND r.type IN ('102-4-6', '103-4-6'))
                        )
                    )
                    OR
                    (
                        d.deputy_type = 'PRO'
                        AND (
                            (d.order_type = 'pfa' AND d.is_hybrid = '0' AND r.type IN ('102-5', '103-5'))
                            OR
                            (d.order_type = 'hw' AND d.is_hybrid = '0' AND r.type IN ('104-5'))
                            OR
                            (d.order_type IN ('hw', 'pfa') AND d.is_hybrid = '1' AND r.type IN ('102-4-5', '103-4-5'))
                        )
                    )
                ) AS report_type_is_compatible
            FROM staging.deputyship d
            LEFT JOIN client c ON d.case_number = c.case_number
            LEFT JOIN report r ON c.id = r.client_id
            WHERE r.start_date >= TO_DATE(d.order_made_date, 'YYYY-MM-DD')
        ) compat
        WHERE report_type_is_compatible = true
        GROUP BY court_order_uid, report_id
        ORDER BY court_order_uid, report_id;
    SQL;

    // NDRs are only assigned for pfa court orders, and only to Lay deputies, hence the WHERE clause
    /** @var string */
    private const COMPATIBLE_NDRS_QUERY = <<<SQL
        SELECT
            d.order_uid AS court_order_uid,
            odr.id AS ndr_id
        FROM staging.deputyship d
        INNER JOIN client c ON d.case_number = c.case_number
        INNER JOIN odr ON c.id = odr.client_id
        WHERE d.order_type = 'pfa'
        AND d.deputy_type = 'LAY'
        AND odr.start_date >= TO_DATE(d.order_made_date, 'YYYY-MM-DD')
        GROUP BY d.order_uid, odr.id
        ORDER BY d.order_uid, odr.id;
    SQL;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StagingSelectedCandidateFactory $candidateFactory,
    ) {
    }

    /**
     * @param string $query SQL query to execute on the db connection
     *
     * @return \Traversable<int, array<string, mixed>>
     *
     * @throws Exception
     */
    private function runQuery(string $query): \Traversable
    {
        $conn = $this->entityManager->getConnection();

        return $conn->executeQuery($query)->iterateAssociative();
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
     * @return \Traversable<StagingSelectedCandidate> Iterator over candidate court_order_report inserts
     *
     * @throws Exception
     */
    public function createCompatibleReportCandidates(): \Traversable
    {
        $result = $this->runQuery(self::COMPATIBLE_REPORTS_QUERY);

        foreach ($result as $row) {
            yield $this->candidateFactory->createInsertOrderReportCandidate(
                ''.$row['court_order_uid'],
                intval(''.$row['report_id'])
            );
        }
    }

    /**
     * Find NDRs which can be associated with a court order.
     *
     * @return \Traversable<StagingSelectedCandidate> Iterator over candidate court_order_ndr inserts
     *
     * @throws Exception
     */
    public function createCompatibleNdrCandidates(): \Traversable
    {
        $result = $this->runQuery(self::COMPATIBLE_NDRS_QUERY);

        foreach ($result as $row) {
            yield $this->candidateFactory->createInsertOrderNdrCandidate(
                ''.$row['court_order_uid'],
                intval(''.$row['ndr_id'])
            );
        }
    }
}

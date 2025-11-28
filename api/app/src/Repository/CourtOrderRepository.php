<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CourtOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourtOrder>
 */
class CourtOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourtOrder::class);
    }

    public function findReportsInfoByUid(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT DISTINCT
        r.id AS "reportId",
        c.firstname AS "firstName",
        c.lastname AS "lastName",
        c.case_number AS "caseNumber",
        (
            SELECT DISTINCT string_agg(co.court_order_uid, ',')
            FROM court_order co
            INNER JOIN court_order_deputy cd ON co.id = cd.court_order_id
            INNER JOIN court_order_report cr ON co.id = cr.court_order_id
            INNER JOIN deputy d ON cd.deputy_id = d.id
            INNER JOIN report re ON cr.report_id = re.id
            WHERE d.deputy_uid = :deputyUid
            AND r.id = re.id
            AND co.status = 'ACTIVE'
        ) AS "courtOrderUid",
        r.type AS "type"
        FROM report r
        INNER JOIN court_order_report cor ON r.id = cor.report_id
        INNER JOIN court_order co ON cor.court_order_id = co.id
        INNER JOIN court_order_deputy cod ON co.id = cod.court_order_id
        INNER JOIN client c ON co.client_id = c.id
        INNER JOIN deputy d ON cod.deputy_id = d.id
        WHERE d.deputy_uid = :deputyUid
        AND cod.is_active = TRUE
        AND cod.deputy_id = d.id
        AND co.status = 'ACTIVE'
        SQL;

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['deputyUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }
}

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

    public function findCourtOrderByUID(string $uid, int $user_id): ?array
    {
        file_put_contents('php://stderr', print_r('USER ID: ' . $user_id, true));
        $sql = <<<SQL
        SELECT DISTINCT co.*
        FROM court_order co
        INNER JOIN court_order_deputy cod ON cod.court_order_id = co.id
        INNER JOIN deputy d ON d.id = cod.deputy_id
        INNER JOIN dd_user u ON u.id = d.user_id
        WHERE co.court_order_uid = :courtOrderUid
        AND cod.is_active = TRUE;
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['courtOrderUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }

    public function findClientByCourtOrderUID(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT c.*
        FROM court_order co
        INNER JOIN client c ON c.id = co.client_id
        INNER JOIN court_order_deputy cod ON cod.court_order_id = co.id
        WHERE co.court_order_uid = :courtOrderUid
        AND cod.is_active = TRUE;
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['courtOrderUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }

    public function findReportsByCourtOrderUID(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT r.*
        FROM court_order co
        INNER JOIN court_order_deputy cod ON cod.court_order_id = co.id
        INNER JOIN court_order_report cor ON cor.court_order_id = co.id
        INNER JOIN report r ON r.id = cor.report_id
        WHERE co.court_order_uid = :courtOrderUid
        AND cod.is_active = TRUE;
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['courtOrderUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }

    public function findDeputiesByUID(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT d.*
        FROM court_order co
        INNER JOIN court_order_deputy cod ON cod.court_order_id = co.id
        INNER JOIN deputy d ON d.id = cod.deputy_id
        WHERE co.court_order_uid = :courtOrderUid
        AND cod.is_active = TRUE;
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['courtOrderUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }

    public function findUserById(int $user_id): ?array
    {
        $sql = <<<SQL
        SELECT
        u.id,
        u.firstname,
        u.lastname,
        u.email,
        u.active,
        u.registration_date,
        u.registration_token,
        u.token_date,
        u.role_name,
        u.phone_main,
        u.phone_alternative,
        u.last_logged_in,
        u.odr_enabled,
        u.ad_managed,
        u.job_title,
        u.agree_terms_use,
        u.agree_terms_use_date,
        u.codeputy_client_confirmed,
        u.address1,
        u.address2,
        u.address3,
        u.address_postcode,
        u.address_country,
        u.address4,
        u.address5,
        u.created_at,
        u.updated_at,
        u.created_by_id,
        u.deletion_protection,
        u.deputy_uid,
        u.pre_register_validated,
        u.registration_route,
        u.is_primary
        FROM dd_user u
        WHERE u.id = :userId
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['userId' => $user_id]);

        $result = $query->fetchAllAssociative();
        return 0 === count($result) ? null : $result;
    }

    public function findReportsInfoByUid(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT DISTINCT
        r.type AS "type",
        c.firstname AS "firstName",
        c.lastname AS "lastName",
        c.case_number AS "caseNumber",
        (
            SELECT DISTINCT string_agg(co.court_order_uid, ', ')
            FROM court_order co
            INNER JOIN court_order_deputy cd ON co.id = cd.court_order_id
            INNER JOIN court_order_report cr ON co.id = cr.court_order_id
            INNER JOIN deputy d ON cd.deputy_id = d.id
            INNER JOIN report re ON cr.report_id = re.id
            WHERE co.status IN ('ACTIVE')
            AND cd.is_active = TRUE
            AND d.deputy_uid = :deputyUid
            AND r.id = re.id
        ) AS "courtOrderUid",
        cod.is_active AS "isActive",
        co.status
        FROM report r
        INNER JOIN court_order_report cor ON r.id = cor.report_id
        INNER JOIN court_order co ON cor.court_order_id = co.id
        INNER JOIN court_order_deputy cod ON co.id = cod.court_order_id
        INNER JOIN client c ON co.client_id = c.id
        INNER JOIN deputy d ON cod.deputy_id = d.id
        WHERE co.status IN ('ACTIVE')
        AND cod.is_active = TRUE
        AND d.deputy_uid = :deputyUid
        AND cod.deputy_id = d.id
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

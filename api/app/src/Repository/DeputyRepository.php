<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deputy>
 */
class DeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    /**
     * Return a mapping from deputy UID to deputy ID.
     *
     * @return array<string, int>
     */
    public function getUidToIdMapping(): array
    {
        $deputies = $this->findAll();

        $mapping = [];

        foreach ($deputies as $deputy) {
            $mapping[$deputy->getDeputyUid()] = $deputy->getId();
        }

        return $mapping;
    }

    /**
     * @return array<int, array<string, array<string, mixed>>>|null
     *
     * @throws Exception
     */
    public function findReportsInfoByUid(int $uid, bool $includeInactive = false): ?array
    {
        $sql = <<<SQL
        SELECT DISTINCT
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
            WHERE d.deputy_uid = :deputyUid
            AND r.id = re.id
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
        SQL;

        // Possibly need to be changed when we have all applicable status
        $sql .= $includeInactive ?
            " AND co.status IN ('INACTIVE', 'ACTIVE')" :
            " AND co.status = 'ACTIVE'";

        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['deputyUid' => (string) $uid]);
        /** @var array<int, array<array-key, string>> $result */
        $result = $query->fetchAllAssociative();

        $data = [];
        foreach ($result as $line) {
            // This is required due to Hybrids, as we want to return single line results, from the above query
            // so we need to split this so we can populate the link to the correct page.
            if (preg_match('{,}', $line['courtOrderUid'])) {
                $courtOrderUids = explode(', ', $line['courtOrderUid']);
                $courtOrderLink = $courtOrderUids[0];
            } else {
                $courtOrderLink = $line['courtOrderUid'];
            }

            $data[] = [
                'client' => [
                    'firstName' => $line['firstName'],
                    'lastName' => $line['lastName'],
                    'caseNumber' => $line['caseNumber'],
                ],
                'report' => [
                    'id' => $line['reportId'] ?? '',
                    'type' => $line['type'],
                ],
                'courtOrder' => [
                    'courtOrderUid' => $line['courtOrderUid'],
                    'courtOrderLink' => $courtOrderLink,
                ],
            ];
        }

        return 0 === count($result) ? null : $data;
    }
}

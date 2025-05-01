<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use App\Entity\Report\Report;
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
     * @return array<int, array<string, array<string, mixed>>>|null
     * @throws Exception
     */
    public function findReportsInfoByUid(int $uid, bool $includeInactive = false): ?array
    {
        $sql = <<<SQL
        SELECT c.firstname AS "firstName",
               c.lastname AS "lastName",
               c.case_number AS "caseNumber",
               co.court_order_uid AS "courtOrderUid",
               r.type AS "type"
        FROM deputy d
        LEFT JOIN client c ON c.deputy_id = d.id
        LEFT JOIN court_order co ON co.client_id = c.id
        LEFT JOIN report r ON r.client_id = c.id
        LEFT JOIN court_order_deputy cod ON cod.deputy_id = d.id
        WHERE cod.is_active = TRUE
        AND d.deputy_uid = :deputyUid
        SQL;

        if ($includeInactive) {
            // Possibly need to be changed when we have all applicable status
            $sql .= ' AND co.status = "INACTIVE"';
        }
        
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['deputyUid' => (string) $uid]);

        $result = $query->fetchAllAssociative();

        $data = [];
        foreach ($result as $line) {
            $data[] = [
                'client' => [
                    'firstName' => $line['firstName'],
                    'lastName' => $line['lastName'],
                    'caseNumber' => $line['caseNumber'],
                ],
                'report' => [
                    'type' => $line['type'],
                ],
                'courtOrder' => [
                    'courtOrderUid' => $line['courtOrderUid']
                ]
            ];
        }

        return 0 === count($result) ? null : $data;
    }
 
    /*
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
}

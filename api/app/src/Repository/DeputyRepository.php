<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use App\Entity\Report\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

class DeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    /**
     * @return array<string>|null
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
        WHERE cod.discharged = FALSE
        AND d.deputy_uid = ':deputyUid' 
        SQL;

        if (!$includeInactive) {
            $sql .= ' AND co.active = TRUE';
        }
        
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['deputyUid' => $uid]);

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
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PreRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PreRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreRegistration::class);
    }

    public function deleteAll()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('App\Entity\PreRegistration', 'p');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(p.id) FROM App\Entity\PreRegistration p')
            ->getSingleScalarResult();
    }

    public function findByRegistrationDetails(string $caseNumber, string $clientLastname, string $deputySurname)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(PreRegistration::class, 'p')
            ->where('LOWER(p.caseNumber) = LOWER(:caseNumber)')
            ->andWhere('LOWER(p.clientLastname) = LOWER(:clientLastname)')
            ->andWhere('LOWER(p.deputySurname) = LOWER(:deputySurname)')
            ->setParameters(['caseNumber' => $caseNumber, 'clientLastname' => $clientLastname, 'deputySurname' => $deputySurname])
            ->getQuery()
            ->getResult();
    }

    public function findByCaseNumber(string $caseNumber)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(PreRegistration::class, 'p')
            ->where('LOWER(p.caseNumber) = LOWER(:caseNumber)')
            ->setParameters(['caseNumber' => $caseNumber])
            ->getQuery()
            ->getResult();
    }

    public function getNewClientsForExistingDeputiesArray(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $newMultiClentsQuery = <<<SQL
        SELECT
            pr.client_case_number AS "Case",
            pr.client_lastname    AS "ClientSurname",
            pr.deputy_uid         AS "DeputyUid",
            pr.deputy_lastname    AS "DeputySurname",
            pr.deputy_address_1   AS "DeputyAddress1",
            pr.deputy_address_2   AS "DeputyAddress2",
            pr.deputy_address_3   AS "DeputyAddress3",
            pr.deputy_address_4   AS "DeputyAddress4",
            pr.deputy_address_5   AS "DeputyAddress5",
            pr.deputy_postcode    AS "DeputyPostcode",
            pr.type_of_report     AS "ReportType",
            pr.order_date         AS "MadeDate",
            pr.order_type         AS "OrderType",
            CASE WHEN pr.is_co_deputy THEN 'yes' ELSE 'no' END AS "CoDeputy",
            pr.created_at         AS "MadeDate",
            pr.hybrid             AS "Hybrid",
            pr.deputy_firstname   AS "DeputyFirstname",
            pr.client_firstname   AS "ClientFirstname",
            pr.client_address_1   AS "ClientAddress1",
            pr.client_address_2   AS "ClientAddress2",
            pr.client_address_3   AS "ClientAddress3",
            pr.client_address_4   AS "ClientAddress4",
            pr.client_address_5   AS "ClientAddress5",
            pr.client_postcode    AS "ClientPostcode"
        FROM pre_registration pr
        LEFT JOIN dd_user u ON pr.deputy_uid::bigint = u.deputy_uid
        LEFT JOIN deputy_case dc ON u.id = dc.user_id
        LEFT JOIN client c ON dc.client_id = c.id
        WHERE c.case_number != pr.client_case_number
        SQL;

        $stmt = $conn->executeQuery($newMultiClentsQuery);
        $result = $stmt->fetchAllAssociative();
        $this->_em->getFilters()->enable('softdeleteable');

        return $result;
    }
}

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
    
    public function findExistingDeputiesMissingAddtionalClients(): array
    {
        $sql = 
        <<<SQL
        WITH pre_reg AS (
            SELECT CAST(deputy_uid AS bigint), 
            client_case_number,
            client_firstname,
            client_lastname,
            client_address1,
            client_address2,
            client_address3,
            client_address4,
            client_address5,
            client_postcode
            FROM pre_registration
        ),
        dep_not_exist AS (
            SELECT deputy_uid FROM pre_reg
            EXCEPT
            (
                SELECT deputy_uid FROM dd_user
                UNION
                SELECT CAST(deputy_uid AS bigint) FROM deputy
            )
        ),
        cdne AS (
            (
                SELECT LOWER(client_case_number) AS case_number 
                FROM pre_reg 
                WHERE deputy_uid NOT IN (SELECT * FROM dep_not_exist)
            )
            EXCEPT
            SELECT case_number FROM client
        )
        SELECT client_case_number,
        client_firstname,
        client_lastname,
        client_address1,
        client_address2,
        client_address3,
        client_address4,
        client_address5,
        client_postcode
        FROM pre_reg WHERE client_case_number IN (SELECT case_number FROM cdne)
        SQL;

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }
}

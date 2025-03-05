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

        /**
         * Query to retrieve the new clients to be made from the PreReg table for existing deputies.
         *
         * Query is comparing the combination of deputy uid and case number (which is akin to a court order, just without the report type);
         * comparison is between combinations in PreReg (essentially Sirius) with combinations from User & Client table (Digideps).
         * This gives us which combinations do not exist in Digideps.
         *
         * Note that because we are deliberately excluding combinations which already exist in digideps, any
         * combos which do exist will cause no change to the database and not throw an error. Also note that we need
         * to handle potential error cases where we don't have the current case+deputy, but then receive it twice in the
         * same CSV file, e.g.
         *
         * case1,newDeputy1,hw
         * case1,newDeputy1,hw
         *
         * Also note that we are not just checking for cases where a deputy already exists: a new deputy might
         * have two or more rows for different cases in the same CSV file, so we can't assume which deputies may
         * be multi-client deputies on the basis of what's currently in the database. For example, we've never seen
         * newDeputy1 or newDeputy2 before and we get these rows in the CSV:
         *
         * case1,newDeputy1,hw
         * case2,newDeputy1,pfa
         * case1,newDeputy2,hw
         *
         * In this situation, case1 should have co-deputies newDeputy1 and newDeputy2, and case2 should have deputy
         * newDeputy1; in addition, newDeputy1 is a multi-client deputy, despite the fact we've never encountered them
         * before in previous CSV uploads.
         */
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
        INNER JOIN dd_user ddu ON pr.deputy_uid = ddu.deputy_uid::varchar(30)
        LEFT JOIN (
            SELECT u.deputy_uid::varchar(30) AS deputy_uid, lower(c.case_number) AS case_number
            FROM dd_user u
            INNER JOIN deputy_case dc ON u.id = dc.user_id
            INNER JOIN client c ON dc.client_id = c.id
        ) dcs
        ON lower(pr.client_case_number) = dcs.case_number AND pr.deputy_uid = dcs.deputy_uid
        WHERE dcs.deputy_uid IS NULL;
        SQL;

        $stmt = $conn->executeQuery($newMultiClentsQuery);

        return $stmt->fetchAllAssociative();
    }
}

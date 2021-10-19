<?php

namespace App\Repository;

use App\Entity\CasRec;
use App\Service\Search\CourtOrderSearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CasRecRepository extends ServiceEntityRepository
{
    /** @var CourtOrderSearchFilter */
    private $filter;

    public function __construct(ManagerRegistry $registry, CourtOrderSearchFilter $filter)
    {
        parent::__construct($registry, CasRec::class);
        $this->filter = $filter;
    }

    /**
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteAllBySource(string $source)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->delete('App\Entity\CasRec', 'cr')
            ->where('cr.source = :source')
            ->setParameter('source', $source);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function searchForCourtOrders($searchTerm = '', $limit = 100)
    {
        if ($searchTerm) {
            $filter = $this->filter->handleSearchTermFilter($searchTerm);
        } else {
            $filter = '';
        }

        $statement = sprintf('
SELECT coalesce(cl.case_number, ca.client_case_number) AS casenumber, INITCAP(coalesce (cl.lastname, ca.client_lastname)) as clientsurname
FROM client as cl
FULL join casrec as ca on cl.case_number = ca.client_case_number
%s
ORDER BY clientsurname ASC
LIMIT %d;',
        $filter, $limit);

        $conn = $this->getEntityManager()->getConnection();

        $courtOrderStmt = $conn->prepare($statement);
        $courtOrderStmt->execute();

        return $courtOrderStmt->fetchAll();
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(c.id) FROM App\Entity\Casrec c')
            ->getSingleScalarResult();
    }
}

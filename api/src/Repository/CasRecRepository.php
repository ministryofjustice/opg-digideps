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

    /**
     * Search Cases.
     *
     * @param string $query     Search query
     * @param string $orderBy   field to order by
     * @param string $sortOrder order of field order ASC|DESC
     * @param int    $limit     number of results to return
     * @param int    $offset    the position of the first result to retrieve
     *
     * @return CasRec[]|array
     */
    public function searchCases($query = '', $orderBy = 'clientLastname', $sortOrder = 'ASC', $limit = 100, $offset = 0)
    {
        $alias = 'c';
        $qb = $this->createQueryBuilder($alias);

        if ($query) {
            $this->filter->handleSearchTermFilter($query, $qb, $alias);
        }

        $limit = ($limit <= 100) ? $limit : 100;
        $qb->setMaxResults($limit);
        $qb->setFirstResult((int) $offset);
        $qb->orderBy($alias.'.'.$orderBy, $sortOrder);

        return $qb->getQuery()->getResult();
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(c.id) FROM App\Entity\Casrec c')
            ->getSingleScalarResult();
    }
}

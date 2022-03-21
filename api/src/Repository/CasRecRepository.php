<?php

namespace App\Repository;

use App\Entity\CasRec;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CasRecRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CasRec::class);
    }

    public function deleteAll()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('App\Entity\CasRec', 'cr');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(c.id) FROM App\Entity\Casrec c')
            ->getSingleScalarResult();
    }
}

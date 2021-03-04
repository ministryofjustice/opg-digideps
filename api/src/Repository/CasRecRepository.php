<?php

namespace App\Repository;

use App\Entity\CasRec;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CasRecRepository extends EntityRepository
{
    /**
     * @param string $source
     * @return mixed
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
}

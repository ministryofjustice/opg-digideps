<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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
            ->delete('AppBundle\Entity\CasRec', 'cr')
            ->where('cr.source = :source')
            ->setParameter('source', $source);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

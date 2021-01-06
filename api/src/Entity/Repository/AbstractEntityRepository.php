<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Common repository methods
 */
abstract class AbstractEntityRepository extends EntityRepository
{
    /**
     * Finds a single entity by a set of criteria. Unfiltered, softdelete disabled.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null the entity instance or NULL if the entity can not be found
     */
    public function findUnfilteredOneBy(array $criteria, array $orderBy = null)
    {
        $this->_em->getFilters()->disable('softdeleteable');

        $result = $this->findOneBy($criteria, $orderBy);

        $this->_em->getFilters()->enable('softdeleteable');

        return $result;
    }
}

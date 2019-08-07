<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class OrganisationRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getAllArray(): array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o FROM AppBundle\Entity\Organisation o');

        return $query->getArrayResult();
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findArrayById(int $id): ?array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o FROM AppBundle\Entity\Organisation o WHERE o.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return count($result) === 0 ? null : $result[0];
    }
}

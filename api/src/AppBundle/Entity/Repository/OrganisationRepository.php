<?php

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class OrganisationRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAllArray(): array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o FROM AppBundle\Entity\Organisation o');

        return $query->getArrayResult();
    }
}

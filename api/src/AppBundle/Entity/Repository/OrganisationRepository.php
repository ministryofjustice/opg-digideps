<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Organisation;
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
            ->createQuery('SELECT o, u, c FROM AppBundle\Entity\Organisation o LEFT JOIN o.users u LEFT JOIN o.clients c WHERE o.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return count($result) === 0 ? null : $result[0];
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteById(int $id): bool
    {
        if (null === ($organisation = $this->find($id))) {
            return false;
        }

        $this->getEntityManager()->remove($organisation);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * @param string $email
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function organisationExists(string $email): bool
    {
        $email = strtolower($email);
        $queryString = 'SELECT COUNT(o.id) FROM AppBundle\Entity\Organisation o WHERE o.emailIdentifier = ?1';
        $queryParams = [1 => $email];

        if (false !== ($atSymbolPosition = strpos($email, '@'))) {
            $domain = substr($email, $atSymbolPosition + 1);
            $queryString .= ' OR o.emailIdentifier = ?2';
            $queryParams[2] = $domain;
        }

        $query = $this
            ->getEntityManager()
            ->createQuery($queryString)
            ->setParameters($queryParams);

        $count = $query->getSingleScalarResult();

        return $count >= 1;
    }

    /**
     * @param string $email
     * @return Organisation|null
     */
    public function findByEmailIdentifier(string $email): ?Organisation
    {
        $email = strtolower($email);
        $queryString = 'SELECT o FROM AppBundle\Entity\Organisation o WHERE o.emailIdentifier = ?1';
        $queryParams = [1 => $email];

        if (false !== ($atSymbolPosition = strpos($email, '@'))) {
            $domain = substr($email, $atSymbolPosition + 1);
            $queryString .= ' OR o.emailIdentifier = ?2';
            $queryParams[2] = $domain;
        }

        $query = $this
            ->getEntityManager()
            ->createQuery($queryString)
            ->setParameters($queryParams);

        $result = $query->getResult();

        return count($result) === 0 ? null : $result[0];
    }
}

<?php

namespace App\Repository;

use App\Entity\Organisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrganisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organisation::class);
    }

    public function getAllArray(): array
    {
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Organisation::class);

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o FROM App\Entity\Organisation o');

        return $query->getArrayResult();
    }

    public function getNonDeletedArray(): array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o FROM App\Entity\Organisation o WHERE o.deletedAt IS NULL');

        return $query->getArrayResult();
    }

    public function getOrgIdAndNames(): array
    {
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Organisation::class);

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o.id, o.name FROM App\Entity\Organisation o');

        return $query->getArrayResult();
    }

    public function findArrayById(int $id): ?array
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o, u, c FROM App\Entity\Organisation o LEFT JOIN o.users u LEFT JOIN o.clients c WHERE o.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return 0 === count($result) ? null : $result[0];
    }

    public function hasActiveEntities(int $id): bool
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o, u FROM App\Entity\Organisation o
            INNER JOIN o.users u
            WHERE o.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        if (count($result) > 0) {
            return true;
        }

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT o, c FROM App\Entity\Organisation o
            INNER JOIN o.clients c
            WHERE o.id = ?1
            AND c.deletedAt is null
            AND c.archivedAt is null')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        if (count($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function organisationExists(string $email): bool
    {
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Organisation::class);

        $email = strtolower($email);
        $queryString = 'SELECT COUNT(o.id) FROM App\Entity\Organisation o WHERE o.emailIdentifier = ?1';
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

    public function findByEmailIdentifier(string $email): ?Organisation
    {
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Organisation::class);

        $email = strtolower($email);
        $queryString = 'SELECT o FROM App\Entity\Organisation o WHERE o.emailIdentifier = ?1';
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

        return 0 === count($result) ? null : $result[0];
    }
}

<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Client;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class UserRepository extends AbstractEntityRepository
{
    /** @var QueryBuilder */
    private $qb;

    /**
     * @param int $id
     * @return null|array
     */
    public function findUserArrayById($id)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT u, c, r FROM AppBundle\Entity\User u LEFT JOIN u.clients c LEFT JOIN c.reports r WHERE u.id = ?1 ORDER BY c.id')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return count($result) === 0 ? null : $result[0];
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function findUsersByQueryParameters(Request $request): ?array
    {
        $this->qb = $this->createQueryBuilder('u');

        $this
            ->handleRoleNameFilter($request)
            ->handleAdManagedFilter($request)
            ->handleNdrEnabledFilter($request)
            ->handleSearchTermFilter($request);

        $order_by = $request->get('order_by', 'id');
        $sort_order = strtoupper($request->get('sort_order', 'DESC'));

        $this->qb
            ->setFirstResult($request->get('offset', 0))
            ->setMaxResults($request->get('limit', 50))
            ->orderBy('u.' . $order_by, $sort_order)
            ->groupBy('u.id');

        return $this->qb->getQuery()->getResult();
    }

    /**
     * @param Request $request
     * @return UserRepository
     */
    private function handleRoleNameFilter(Request $request): UserRepository
    {
        if (! ($roleName = $request->get('role_name'))) {
            return $this;
        }

        $operand = (strpos($roleName, '%')) !== false ? 'LIKE' : '=';

        $this
            ->qb
            ->andWhere(sprintf('u.roleName %s :role', $operand))
            ->setParameter('role', $roleName);

        return $this;
    }

    /**
     * @param Request $request
     * @return UserRepository
     */
    private function handleAdManagedFilter(Request $request): UserRepository
    {
        if ($request->get('ad_managed')) {
            $this->qb->andWhere('u.adManaged = true');
        }

        return $this;
    }

    /**
     * @param Request $request
     * @return UserRepository
     */
    private function handleNdrEnabledFilter(Request $request): UserRepository
    {
        if ($request->get('ndr_enabled')) {
            $this->qb->andWhere('u.ndrEnabled = true');
        }

        return $this;
    }

    /**
     * @param Request $request
     * @return UserRepository
     */
    private function handleSearchTermFilter(Request $request): UserRepository
    {
        if (! ($searchTerm = $request->get('q'))) {
            return $this;
        }

        if (Client::isValidCaseNumber($searchTerm)) {
            $this->qb->leftJoin('u.clients', 'c');
            $this->qb->andWhere('lower(c.caseNumber) = :cn');
            $this->qb->setParameter('cn', strtolower($searchTerm));
        } else {
            $this->qb->leftJoin('u.clients', 'c');

            $searchTerms = explode(' ', $searchTerm);
            $includeClients = (bool) $request->get('include_clients');

            if (count($searchTerms) === 1) {
                $this->addBroadMatchFilter($searchTerm, $includeClients);
            } else {
                $this->addFullNameExactMatchFilter($searchTerms[0], $searchTerms[1], $includeClients);
            }
        }

        return $this;
    }

    /**
     * @param string $searchTerm
     * @param bool $includeClients
     * @return string
     */
    function addBroadMatchFilter(string $searchTerm, bool $includeClients)
    {
        $nameBasedQuery = '(lower(u.email) LIKE :qLike OR lower(u.firstname) LIKE :qLike OR lower(u.lastname) LIKE :qLike)';

        if ($includeClients) {
            $nameBasedQuery .= ' OR (lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike)';
        }

        $this->qb->setParameter('qLike', '%' . strtolower($searchTerm) . '%');
        $this->qb->andWhere($nameBasedQuery);
    }

    /**
     * @param string $firstName
     * @param string $lastname
     * @param bool $includeClients
     * @return string
     */
    function addFullNameExactMatchFilter(string $firstName, string $lastname, bool $includeClients)
    {
        $nameBasedQuery = '(lower(u.firstname) = :firstname AND lower(u.lastname) = :lastname)';

        if ($includeClients) {
            $nameBasedQuery .= ' OR (lower(c.firstname) = :firstname AND lower(c.lastname) = :lastname)';
        }

        $this->qb->setParameter('firstname', strtolower($firstName));
        $this->qb->setParameter('lastname', strtolower($lastname));

        $this->qb->andWhere($nameBasedQuery);
    }
}

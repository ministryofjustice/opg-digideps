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
     * @param $id
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

            $nameBasedQuery = 'lower(u.email) LIKE :qLike OR lower(u.firstname) LIKE :qLike OR lower(u.lastname) LIKE :qLike';
            if ($request->get('include_clients')) {
                $nameBasedQuery .= ' OR lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike';
            }

            $this->qb->andWhere($nameBasedQuery);
            $this->qb->setParameter('qLike', '%' . strtolower($searchTerm) . '%');
        }

        return $this;
    }
}

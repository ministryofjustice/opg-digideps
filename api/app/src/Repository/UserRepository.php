<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /** @var QueryBuilder */
    private $qb;

    public function __construct(ManagerRegistry $registry, private SerializerInterface $serializer)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int $id
     *
     * @return array|null
     */
    public function findUserArrayById($id)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT u, c, r FROM App\Entity\User u LEFT JOIN u.clients c LEFT JOIN c.reports r WHERE u.id = ?1 ORDER BY c.id')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();

        return 0 === count($result) ? null : $result[0];
    }

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
            ->orderBy('u.'.$order_by, $sort_order)
            ->groupBy('u.id');

        if ($request->get('filter_by_ids')) {
            $this->qb->where(sprintf('u.id IN (%s)', $request->get('filter_by_ids')));
        }

        return $this->qb->getQuery()->getResult();
    }

    private function handleRoleNameFilter(Request $request): UserRepository
    {
        if (!($roleName = $request->get('role_name'))) {
            return $this;
        }

        $operand = false !== strpos($roleName, '%') ? 'LIKE' : '=';

        $this
            ->qb
            ->andWhere(sprintf('u.roleName %s :role', $operand))
            ->setParameter('role', $roleName);

        return $this;
    }

    private function handleAdManagedFilter(Request $request): UserRepository
    {
        if ($request->get('ad_managed')) {
            $this->qb->andWhere('u.adManaged = true');
        }

        return $this;
    }

    private function handleNdrEnabledFilter(Request $request): UserRepository
    {
        if ($request->get('ndr_enabled')) {
            $this->qb->andWhere('u.ndrEnabled = true');
        }

        return $this;
    }

    private function handleSearchTermFilter(Request $request): UserRepository
    {
        if (!($searchTerm = $request->get('q'))) {
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

            if (1 === count($searchTerms)) {
                $this->addBroadMatchFilter($searchTerm, $includeClients);
            } else {
                $this->addFullNameBestMatchFilter($searchTerms[0], $searchTerms[1], $includeClients);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function addBroadMatchFilter(string $searchTerm, bool $includeClients)
    {
        $nameBasedQuery = '(lower(u.email) LIKE :qLike OR lower(u.firstname) LIKE :qLike OR lower(u.lastname) LIKE :qLike)';

        if ($includeClients) {
            $nameBasedQuery .= ' OR (lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike)';
        }

        $this->qb->setParameter('qLike', '%'.strtolower($searchTerm).'%');
        $this->qb->andWhere($nameBasedQuery);
    }

    /**
     * @return string
     */
    public function addFullNameBestMatchFilter(string $firstName, string $otherName, bool $includeClients)
    {
        $nameBasedQuery = '(lower(u.firstname) LIKE :firstname AND (lower(u.firstname) LIKE :othername OR lower(u.lastname) LIKE :othername))';

        if ($includeClients) {
            $nameBasedQuery .= ' OR (lower(c.firstname) LIKE :firstname AND (lower(c.firstname) LIKE :othername OR lower(c.lastname) LIKE :othername))';
        }

        $this->qb->setParameter('firstname', '%'.strtolower($firstName.'%'));
        $this->qb->setParameter('othername', '%'.strtolower($otherName.'%'));

        $this->qb->andWhere($nameBasedQuery);
    }

    /**
     * @return int[]
     *
     * @throws Exception
     */
    public function findInactive($role = User::ROLE_LAY_DEPUTY): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<sql
        SELECT u.id
        FROM dd_user u
        LEFT JOIN deputy_case dc ON dc.user_id = u.id
        LEFT JOIN client c ON c.id = dc.client_id AND (c.deleted_at IS NULL)
        WHERE (
            -- Abandoned Registration process, password set
            u.registration_token IS NOT NULL
            AND u.token_date < date_trunc('day', CURRENT_DATE - INTERVAL '60' day)
            AND u.last_logged_in < date_trunc('day', CURRENT_DATE - INTERVAL '60' day)
            AND u.registration_date IS NULL
            -- Abandoned Registration process, no password set
            OR u.registration_token IS NOT NULL
            AND u.token_date < date_trunc('day', CURRENT_DATE - INTERVAL '30' day)
            AND u.last_logged_in IS NULL
            -- Standard no activity within N days
            OR u.registration_date < date_trunc('day', CURRENT_DATE - INTERVAL '30' day)
        )
        AND u.role_name = :role
        AND NOT EXISTS (
            SELECT TRUE FROM user_research_response urr WHERE urr.user_id = u.id
        ) AND NOT EXISTS (
            SELECT TRUE FROM report r WHERE r.client_id = c.id
        ) AND NOT EXISTS (
            SELECT TRUE FROM odr n WHERE n.client_id = c.id
        ) AND NOT EXISTS (
            SELECT TRUE FROM dd_user du WHERE du.created_by_id = u.id
        ) AND NOT EXISTS (
            SELECT TRUE FROM deputy d WHERE d.user_id = u.id
        )
        sql;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['role' => $role]);

        return $result->fetchFirstColumn();
    }

    public function findActiveLaysInLastYear(): array
    {
        $oneYearAgo = (new \DateTime())->modify('-1 Year')->format('Y-m-d');

        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
        SELECT u.id,
        u.firstname as user_first_name,
        u.lastname as user_last_name,
        u.email as user_email,
        u.phone_main as user_phone_number,
        u.registration_date,
        u.last_logged_in,
        c.firstname as client_first_name,
        c.lastname as client_last_name,
        COUNT(r.id) as submitted_reports
        FROM dd_user as u
        LEFT JOIN deputy_case as dc on u.id = dc.user_id
        LEFT JOIN client as c on dc.client_id = c.id
        LEFT JOIN report as r on c.id = r.client_id
        WHERE r.submit_date is not null AND u.role_name = 'ROLE_LAY_DEPUTY' AND u.last_logged_in >= :oneYearAgo
        GROUP BY u.id, u.firstname, u.lastname, u.email, u.registration_date, u.last_logged_in, c.firstname, c.lastname
        SQL;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['oneYearAgo' => $oneYearAgo]);

        return $result->fetchAllAssociative();
    }

    /**
     * Required to avoid lazy loading which is incompatible with Symfony Serializer.
     */
    public function findUserByEmail(string $email)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
SELECT * FROM dd_user as u
WHERE lower(u.email) = lower(:email)
SQL;

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['email' => $email]);

        return $this->serializer->deserialize(json_encode($result->fetchAssociative()), 'App\Entity\User', 'json');
    }

    public function getAllAdminAccounts()
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roleName IN('ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER')";

        $query = $this
            ->getEntityManager()
            ->createQuery($dql);

        return $query->getResult();
    }

    public function getAllAdminAccountsCreatedButNotActivatedWithin(string $timeframe)
    {
        $date = (new \DateTime())->modify($timeframe);

        $dql = "SELECT u FROM App\Entity\User u WHERE u.roleName IN('ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER')
                AND u.lastLoggedIn IS NULL
                AND u.registrationDate < :date ";

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter('date', $date);

        return $query->getResult();
    }

    public function getAllActivatedAdminAccounts()
    {
        $dql = "SELECT u FROM App\Entity\User u WHERE u.roleName IN('ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER')
                AND u.lastLoggedIn IS NOT NULL";

        $query = $this
            ->getEntityManager()
            ->createQuery($dql);

        return $query->getResult();
    }

    public function getAllAdminAccountsUsedWithin(string $timeframe)
    {
        $date = (new \DateTime())->modify($timeframe);

        $dql = "SELECT u FROM App\Entity\User u WHERE u.roleName IN('ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN_MANAGER')
                AND u.lastLoggedIn > :date ";

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter('date', $date);

        return $query->getResult();
    }

    public function getAllAdminAccountsNotUsedWithin(string $timeframe)
    {
        return $this->getAllRoleBasedUsers(['ROLE_ADMIN', 'ROLE_ADMIN_MANAGER', 'ROLE_SUPER_ADMIN'], $timeframe);
    }

    private function getAllRoleBasedUsers(array $roles, string $timeframe)
    {
        $date = (new \DateTime())->modify($timeframe);

        $dql = 'SELECT u FROM App\Entity\User u WHERE u.roleName IN(:roles) AND u.lastLoggedIn < :date ';

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter('date', $date)
            ->setParameter('roles', $roles);

        return $query->getResult();
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // set the new encoded password on the User object
        $user->setPassword($newEncodedPassword);

        // execute the queries on the database
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function deleteInactiveAdminUsers(array $inactiveAdminUserIds)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);

        $sql = "DELETE FROM dd_user WHERE id IN (:ids) AND last_logged_in < current_date - INTERVAL '24' month";
        $params = [
            'ids' => $inactiveAdminUserIds,
        ];

        $stmt = $em->createNativeQuery($sql, $rsm);
        $result = $stmt->setParameters($params);

        return $result->getResult();
    }

    public function getAllDeletionProtectedAccounts()
    {
        $dql = 'SELECT u.id FROM App\Entity\User u WHERE u.deletionProtection = true';

        $stmt = $this->getEntityManager()->createQuery($dql);

        return $stmt->getResult();
    }

    public function findByFiltersWithCounts(
        $q,
        $offset,
        $limit,
        $id
    ) {
        // BASE QUERY BUILDER with filters (for both count and results)
        $qb = $this->createQueryBuilder('u');
        $qb->leftJoin('u.organisations', 'o');
        $qb->andWhere('o.id = :id');
        $qb->setParameter('id', $id);

        // search filter
        if ($q) {
            $qb->andWhere(implode(' OR ', [
                'lower(u.firstname) LIKE :qLike',
                'lower(u.lastname) LIKE :qLike',
            ]));

            $qb->setParameter('qLike', '%'.strtolower($q).'%');
            $qb->setParameter('q', strtolower($q));
        }

        // get results (base query + ordered + pagination + status filter)
        $qbSelect = clone $qb;
        $qbSelect->select('u');
        $qbSelect
            ->addOrderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(User::class); // disable softdelete for createdBy, needed from admin area
        $records = $qbSelect->getQuery()->getResult(); /* @var $records User[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // run counts on the base query for each status (new/archived)
        $qbCount = clone $qb;
        $queryCount = $qbCount->select('count(DISTINCT u.id)')->getQuery();
        $count = $queryCount->getSingleScalarResult();

        return [
            'records' => $records,
            'count' => $count,
        ];
    }

    public function findDeputyUidsForClient(int $clientId)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery("SELECT u.deputyUid FROM App\Entity\User u LEFT JOIN u.clients c where c.id = ?1 AND u.deputyUid IS NOT NULL ORDER BY u.id")
            ->setParameter(1, $clientId);

        return $query->getArrayResult();
    }
}

<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\User;
use App\Service\Search\ClientSearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;

class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly ClientSearchFilter $filter)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Search Clients.
     *
     * @param string $query     Search query
     * @param string $orderBy   field to order by
     * @param string $sortOrder order of field order ASC|DESC
     * @param int    $limit     number of results to return
     * @param int    $offset
     *
     * @return Client[]|array
     */
    public function searchClients($query = '', $orderBy = 'lastname', $sortOrder = 'ASC', $limit = 100, $offset = 0)
    {
        /** @var SoftDeleteableFilter $filter */
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Client::class);

        $alias = 'c';
        $qb = $this->createQueryBuilder($alias);

        if ($query) {
            $this->filter->handleSearchTermFilter($query, $qb, $alias);
        }

        $limit = ($limit <= 100) ? $limit : 100;
        $qb->setMaxResults($limit);
        $qb->setFirstResult((int) $offset);
        $qb->orderBy($alias.'.'.$orderBy, $sortOrder);

        $this->_em->getFilters()->enable('softdeleteable');

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function saveUserToClient(User $user, int $clientId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $conn->executeQuery(
            'INSERT INTO deputy_case (client_id, user_id) VALUES (?, ?) ON CONFLICT DO NOTHING',
            [$clientId, $user->getId()]
        );
    }

    /**
     * @param int $id
     */
    public function getArrayById($id)
    {
        /** @var SoftDeleteableFilter $filter */
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Client::class);

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT c, r, ndr, o, nd, u FROM App\Entity\Client c LEFT JOIN c.reports r LEFT JOIN c.ndr ndr LEFT JOIN c.deputy nd LEFT JOIN c.organisation o LEFT JOIN c.users u WHERE c.id = ?1')
            ->setParameter(1, $id);

        $result = $query->getArrayResult();
        $this->_em->getFilters()->enable('softdeleteable');

        return 0 === count($result) ? null : $result[0];
    }

    /**
     * @return array<mixed>|null
     */
    public function getArrayByCaseNumber($caseNumber)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT c FROM App\Entity\Client c WHERE LOWER(c.caseNumber) = LOWER(?1)')
            ->setParameter(1, $caseNumber);

        $result = $query->getArrayResult();

        return 0 === count($result) ? null : $result[0];
    }

    public function findByCaseNumber(string $caseNumber): ?Client
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT c FROM App\Entity\Client c WHERE LOWER(c.caseNumber) = LOWER(:caseNumber)')
            ->setParameter('caseNumber', $caseNumber)
            ->getOneOrNullResult();
    }

    public function findByCaseNumberIncludingDischarged(string $caseNumber): mixed
    {
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Client::class);

        $clients = $this
            ->getEntityManager()
            ->createQuery('SELECT c FROM App\Entity\Client c WHERE LOWER(c.caseNumber) = LOWER(:caseNumber)')
            ->setParameter('caseNumber', $caseNumber)
            ->getResult();

        $this->_em->getFilters()->enable('softdeleteable');

        return $clients;
    }

    public function findByIdIncludingDischarged(int $id): ?Client
    {
        /** @var SoftDeleteableFilter $filter */
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Client::class);

        $client = $this->find($id);

        $this->_em->getFilters()->enable('softdeleteable');

        return $client;
    }

    public function findByFiltersWithCounts(
        $q,
        $offset,
        $limit,
        $id,
    ) {
        // BASE QUERY BUILDER with filters (for both count and results)
        $qb = $this->createQueryBuilder('c');
        $qb->andWhere('c.organisation = :id');
        $qb->setParameter('id', $id);

        // search filter
        if ($q) {
            $qb->andWhere(implode(' OR ', [
                'lower(c.firstname) LIKE :qLike',
                'lower(c.lastname) LIKE :qLike',
            ]));

            $qb->setParameter('qLike', '%'.strtolower($q).'%');
            $qb->setParameter('q', strtolower($q));
        }

        // get results (base query + ordered + pagination + status filter)
        $qbSelect = clone $qb;
        $qbSelect->select('c');
        $qbSelect
            ->addOrderBy('c.lastname', 'ASC')
            ->addOrderBy('c.firstname', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Client::class); // disable softdelete for createdBy, needed from admin area
        $records = $qbSelect->getQuery()->getResult(); /* @var $records User[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // run counts on the base query for each status (new/archived)
        $qbCount = clone $qb;
        $queryCount = $qbCount->select('count(DISTINCT c.id)')->getQuery();
        $count = $queryCount->getSingleScalarResult();

        return [
            'records' => $records,
            'count' => $count,
        ];
    }

    /**
     * @return mixed|null
     */
    public function findExistingDeputyCases(string $caseNumber, string $deputyNumber)
    {
        $deputyCaseQuery = '
                SELECT dc.client_id, dc.user_id
                FROM deputy_case dc
                INNER JOIN client c ON dc.client_id = c.id
                INNER JOIN dd_user du ON dc.user_id = du.id
                WHERE LOWER(c.case_number) = LOWER(:case_number) AND LOWER(du.deputy_no) = LOWER(:deputy_no);
        ';

        $conn = $this->getEntityManager()->getConnection();
        $statsStmt = $conn->prepare($deputyCaseQuery);
        $result = $statsStmt->executeQuery(['case_number' => $caseNumber, 'deputy_no' => $deputyNumber]);
        $deputyCaseResults = $result->fetchAllAssociative();

        return 0 === count($deputyCaseResults) ? null : $deputyCaseResults[0];
    }

    public function getAllClientsAndReportsByDeputyUid(int $deputyUid)
    {
        $query = $this
            ->getEntityManager()
            ->createQuery("SELECT c FROM App\Entity\Client c LEFT JOIN c.users u where u.deputyUid = ?1 ORDER BY c.id")
            ->setParameter(1, $deputyUid);

        return $query->getResult();
    }
}

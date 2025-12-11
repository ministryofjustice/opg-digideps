<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deputy>
 */
class DeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    /**
     * Return a mapping from deputy UID to deputy ID.
     *
     * @return array<string, int>
     */
    public function getUidToIdMapping(): array
    {
        $deputies = $this->findAll();

        $mapping = [];

        foreach ($deputies as $deputy) {
            $mapping[$deputy->getDeputyUid()] = $deputy->getId();
        }

        return $mapping;
    }

    public function findDeputiesByCourtOrderUID(string $uid): ?array
    {
        $sql = <<<SQL
        SELECT DISTINCT d.*
        FROM court_order co
        INNER JOIN court_order_deputy cod ON cod.court_order_id = co.id
        INNER JOIN deputy d ON d.id = cod.deputy_id
        WHERE co.court_order_uid = :courtOrderUid
        AND cod.is_active = TRUE;
        SQL;
        $query = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->executeQuery(['courtOrderUid' => $uid]);

        $result = $query->fetchAllAssociative();

        return 0 === count($result) ? null : $result;
    }

    public function save(Deputy $deputy): void
    {
        $this->_em->persist($deputy);
        $this->_em->flush();
    }
}

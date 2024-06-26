<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserResearch\UserResearchResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class UserResearchResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResearchResponse::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(UserResearchResponse $userResearchResponse, User $user): void
    {
        $userResearchResponse->setUser($user);
        $this->getEntityManager()->persist($userResearchResponse);
        $this->getEntityManager()->flush();
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     *
     * @return int|mixed|string
     */
    public function getAllFilteredByDate(\DateTime $from, \DateTime $to)
    {
        $qb = $this
            ->createQueryBuilder('urr')
            ->select('urr', 'u', 's', 'rt')
            ->leftJoin('urr.satisfaction', 's')
            ->leftJoin('urr.user', 'u')
            ->leftJoin('urr.researchType', 'rt')
            ->where('urr.created > :from')->setParameter('from', $from)
            ->andWhere('urr.created < :to')->setParameter('to', $to);

        return $qb->getQuery()->getArrayResult();
    }
}

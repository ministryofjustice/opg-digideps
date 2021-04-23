<?php declare(strict_types=1);


namespace App\Repository;

use App\Entity\User;
use App\Entity\UserResearch\UserResearchResponse;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserResearchResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResearchResponse::class);
    }

    /**
     * @param UserResearchResponse $userResearchResponse
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(UserResearchResponse $userResearchResponse, User $user): void
    {
        $userResearchResponse->setUser($user);
        $this->getEntityManager()->persist($userResearchResponse);
        $this->getEntityManager()->flush();
    }

    /**
     * @param DateTime|null $from
     * @param DateTime|null $to
     * @return int|mixed|string
     */
    public function getAllFilteredByDate(DateTime $from, DateTime $to)
    {
        $qb = $this
            ->createQueryBuilder('urr')
            ->leftJoin('urr.satisfaction', 's')
            ->where('urr.created > :from')->setParameter('from', $from)
            ->andWhere('urr.created < :to')->setParameter('to', $to);

        return $qb->getQuery()->getResult();
    }
}

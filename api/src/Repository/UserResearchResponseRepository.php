<?php declare(strict_types=1);


namespace App\Repository;

use App\Entity\User;
use App\Entity\UserResearch\UserResearchResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserResearchResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserResearchResponse::class);
    }

    public function create(UserResearchResponse $userResearchResponse, User $user)
    {
        $userResearchResponse->setUser($user);
        $this->getEntityManager()->persist($userResearchResponse);
        $this->getEntityManager()->flush();
    }
}

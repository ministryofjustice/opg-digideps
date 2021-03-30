<?php declare(strict_types=1);


namespace App\Entity\Repository;

use App\Entity\User;
use App\Entity\UserResearch\UserResearchResponse;

class UserResearchResponseRepository extends AbstractEntityRepository
{
    public function create(UserResearchResponse $userResearchResponse, User $user)
    {
        $userResearchResponse->setUser($user);
        $this->getEntityManager()->persist($userResearchResponse);
        $this->getEntityManager()->flush();
    }
}
